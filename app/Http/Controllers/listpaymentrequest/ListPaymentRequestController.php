<?php

namespace App\Http\Controllers\listpaymentrequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\attendance_payments;
use App\Models\attendance_payments_time_schedule;
use App\Models\generated_receipt;
use App\Models\tbl_attendance;
use App\Models\students;
use App\Models\events;
use App\Models\permission_settings;
use App\Models\permission_settings_list;

class ListPaymentRequestController extends Controller
{
    public function listPaymentRequest()
    {
        // Get current logged-in user or student
        $currentUser = auth('web')->user();
        $currentStudent = auth('students')->user();
        
        // Build query for payments that have time schedules
        $paymentsQuery = attendance_payments::with([
            'students.college',
            'students.program',
            'students.organization',
            'events'
        ])
            ->whereHas('timeSchedules', function($query) {
                $query->where('status', 'active');
            })
            ->where('status', 'active');
        
        // Filter logic based on who is logged in
        if ($currentStudent) {
            // If logged in as student, check if they have permission settings
            $permissionSetting = permission_settings::where('students_id', $currentStudent->id)
                ->whereNull('users_id')
                ->where('status', 'active')
                ->first();
            
            if ($permissionSetting) {
                // Check if they have modules assigned (permission_settings_list)
                $hasModules = permission_settings_list::where('permission_settings_id', $permissionSetting->id)
                    ->where('status', 'active')
                    ->exists();
                
                if ($hasModules) {
                    // Student has permissions - filter by their college_id, program_id, or organization_id
                    $studentCollegeId = $currentStudent->college_id;
                    $studentProgramId = $currentStudent->program_id;
                    $studentOrganizationId = $currentStudent->organization_id;
                    
                    // Filter payments to only show students with same college, program, AND organization
                    $paymentsQuery->whereHas('students', function($query) use ($studentCollegeId, $studentProgramId, $studentOrganizationId) {
                        if ($studentCollegeId) {
                            $query->where('college_id', $studentCollegeId);
                        }
                        
                        if ($studentProgramId) {
                            $query->where('program_id', $studentProgramId);
                        }
                        
                        if ($studentOrganizationId) {
                            $query->where('organization_id', $studentOrganizationId);
                        }
                        
                        // If no conditions at all, show nothing
                        if (!$studentCollegeId && !$studentProgramId && !$studentOrganizationId) {
                            $query->whereRaw('1 = 0');
                        }
                    });
                    
                } else {
                    // Student has permission setting but no modules - show nothing
                    $paymentsQuery->whereRaw('1 = 0');
                }
            } else {
                // Student has no permission settings - show nothing
                $paymentsQuery->whereRaw('1 = 0');
            }
        } elseif ($currentUser) {
            // If logged in as user, check their permission settings
            $permissionSetting = permission_settings::where('users_id', $currentUser->id)
                ->whereNull('students_id')
                ->where('status', 'active')
                ->first();
            
            if ($permissionSetting) {
                // Check if they have modules assigned (permission_settings_list)
                $hasModules = permission_settings_list::where('permission_settings_id', $permissionSetting->id)
                    ->where('status', 'active')
                    ->exists();
                
                if ($hasModules) {
                    // User has permissions - get students from permission settings that have students_id
                    // These are the students the user has access to
                    $permissionStudents = permission_settings::where('users_id', $currentUser->id)
                        ->whereNotNull('students_id')
                        ->where('status', 'active')
                        ->pluck('students_id')
                        ->toArray();
                    
                    if (!empty($permissionStudents)) {
                        // Get students from permission settings
                        $allowedStudents = students::whereIn('id', $permissionStudents)
                            ->where('status', 'active')
                            ->get();
                        
                        if ($allowedStudents->isNotEmpty()) {
                            // Collect unique college_id, program_id, organization_id from allowed students
                            $allowedCollegeIds = $allowedStudents->pluck('college_id')->filter()->unique()->values()->toArray();
                            $allowedProgramIds = $allowedStudents->pluck('program_id')->filter()->unique()->values()->toArray();
                            $allowedOrganizationIds = $allowedStudents->pluck('organization_id')->filter()->unique()->values()->toArray();
                            
                            // Filter payments to only show students matching the permission settings
                            // Match by college_id AND program_id AND organization_id
                            if (!empty($allowedCollegeIds) || !empty($allowedProgramIds) || !empty($allowedOrganizationIds)) {
                                $paymentsQuery->whereHas('students', function($query) use ($allowedCollegeIds, $allowedProgramIds, $allowedOrganizationIds) {
                                    if (!empty($allowedCollegeIds)) {
                                        $query->whereIn('college_id', $allowedCollegeIds);
                                    }
                                    
                                    if (!empty($allowedProgramIds)) {
                                        $query->whereIn('program_id', $allowedProgramIds);
                                    }
                                    
                                    if (!empty($allowedOrganizationIds)) {
                                        $query->whereIn('organization_id', $allowedOrganizationIds);
                                    }
                                });
                            } else {
                                // If no valid IDs found, return empty result
                                $paymentsQuery->whereRaw('1 = 0');
                            }
                        } else {
                            // No valid students found - show nothing
                            $paymentsQuery->whereRaw('1 = 0');
                        }
                    } else {
                        // User has permission setting with modules but no students assigned - show all (no filtering)
                        // No additional filtering needed
                    }
                } else {
                    // User has permission setting but no modules - show all (no filtering)
                    // No additional filtering needed
                }
            } else {
                // User has no permission settings - show all payment requests (no filtering)
                // No additional filtering needed
            }
        }
        
        $paymentsWithSchedules = $paymentsQuery->get();

        // Build formatted payments array
        $formattedPayments = [];

        // Process each payment that has time schedules
        foreach ($paymentsWithSchedules as $payment) {
            $student = $payment->students;
            $event = $payment->events;
            
            // Skip if student or event is missing
            if (!$student || !$event) {
                continue;
            }
            
            // Get time schedules for this payment
            $timeSchedules = attendance_payments_time_schedule::where('attendance_payments_id', $payment->id)
                ->where('status', 'active')
                ->orderBy('log_time', 'asc')
                ->get();

            // Skip if no time schedules (shouldn't happen due to whereHas, but just in case)
            if ($timeSchedules->isEmpty()) {
                continue;
            }

            // Count time in (workstate = 0) and time out (workstate = 1)
            // Handle workstate as string ("0" or "1") or integer (0 or 1)
            $timeInCount = $timeSchedules->filter(function($schedule) {
                $ws = $schedule->workstate;
                return $ws === "0" || $ws === 0 || $ws === "time_in";
            })->count();
            
            $timeOutCount = $timeSchedules->filter(function($schedule) {
                $ws = $schedule->workstate;
                return $ws === "1" || $ws === 1 || $ws === "time_out";
            })->count();

            // Format schedule periods
            $schedulePeriods = $timeSchedules->pluck('type_of_schedule_pay')->unique()->values()->toArray();
            $schedulePeriodsText = implode(', ', array_map(function($period) {
                return ucfirst(str_replace('_', ' ', $period));
            }, $schedulePeriods));

            $formattedPayments[] = [
                'id' => $payment->id,
                'student_id' => $payment->students_id,
                'student_name' => $student ? $student->student_name : 'N/A',
                'student_id_number' => $student ? $student->id_number : 'N/A',
                'student_photo' => $student ? $student->photo : null,
                'college' => $student && $student->college ? $student->college->college_name : 'N/A',
                'program' => $student && $student->program ? $student->program->program_name : 'N/A',
                'event_id' => $payment->events_id,
                'event_name' => $event ? $event->event_name : 'N/A',
                'amount_paid' => $payment->amount_paid ?? 0,
                'payment_status' => $payment->payment_status ?? 'pending',
                'schedule_periods' => $schedulePeriodsText ?: 'N/A',
                'time_in_count' => $timeInCount,
                'time_out_count' => $timeOutCount,
                'total_schedules' => $timeSchedules->count(),
                'created_at' => $payment->created_at,
                'time_schedules' => $timeSchedules,
            ];
        }

        // Sort by created_at descending
        usort($formattedPayments, function($a, $b) {
            $dateA = $a['created_at'] instanceof \Carbon\Carbon ? $a['created_at'] : \Carbon\Carbon::parse($a['created_at']);
            $dateB = $b['created_at'] instanceof \Carbon\Carbon ? $b['created_at'] : \Carbon\Carbon::parse($b['created_at']);
            // Compare timestamps for descending order (newest first)
            return $dateB->timestamp <=> $dateA->timestamp;
        });

        // Manual pagination
        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $total = count($formattedPayments);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedPayments = array_slice($formattedPayments, $offset, $perPage);

        // Create paginator-like object for view compatibility
        $paymentRequests = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedPayments,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('list_payment_request.list_payment_request', [
            'paymentRequests' => $paymentRequests,
            'formattedPayments' => $paginatedPayments,
        ]);
    }

    public function getPaymentDetails($id)
    {
        try {
            // Get payment with relationships
            $payment = attendance_payments::with([
                'students.college',
                'students.program',
                'students.organization',
                'events'
            ])
            ->where('id', $id)
            ->where('status', 'active')
            ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }

            // Get time schedules
            $timeSchedules = attendance_payments_time_schedule::where('attendance_payments_id', $id)
                ->where('status', 'active')
                ->orderBy('log_time', 'asc')
                ->get();

            // Format response
            $student = $payment->students;
            $event = $payment->events;

            // Get original amount before waiver (amount_paid + waiver_amount)
            $originalAmount = ($payment->amount_paid ?? 0) + ($payment->waiver_amount ?? 0);

            return response()->json([
                'success' => true,
                'payment' => [
                    'id' => $payment->id,
                    'amount_paid' => $payment->amount_paid ?? 0,
                    'waiver_amount' => $payment->waiver_amount ?? 0,
                    'waiver_reason' => $payment->waiver_reason ?? null,
                    'original_amount' => $originalAmount,
                    'payment_status' => $payment->payment_status ?? 'pending',
                    'category' => $payment->category ?? null,
                    'ref_number' => $payment->ref_number ?? null,
                    'created_at' => $payment->created_at ? $payment->created_at->format('M d, Y h:i A') : null,
                ],
                'student' => [
                    'student_name' => $student ? $student->student_name : 'N/A',
                    'id_number' => $student ? $student->id_number : 'N/A',
                    'college' => $student && $student->college ? $student->college->college_name : 'N/A',
                    'program' => $student && $student->program ? $student->program->program_name : 'N/A',
                    'organization' => $student && $student->organization ? $student->organization->organization_name : 'N/A',
                ],
                'event' => [
                    'event_name' => $event ? $event->event_name : 'N/A',
                    'event_description' => $event ? $event->event_description : 'N/A',
                ],
                'time_schedules' => $timeSchedules->map(function($schedule) {
                    return [
                        'id' => $schedule->id,
                        'type_of_schedule_pay' => $schedule->type_of_schedule_pay,
                        'log_time' => $schedule->log_time,
                        'workstate' => $schedule->workstate,
                    ];
                })->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load payment details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approvePayment(Request $request, $id)
    {
        try {
            $request->validate([
                'amount_paid' => 'nullable|numeric|min:0',
                'category' => 'required|in:cash,gcash',
                'ref_number' => 'required_if:category,gcash|nullable|string|max:255'
            ]);

            $payment = attendance_payments::where('id', $id)
                ->where('status', 'active')
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }

            // Update amount_paid if provided
            if ($request->has('amount_paid') && $request->amount_paid !== null) {
                $payment->amount_paid = $request->amount_paid;
            }

            // Update category
            $payment->category = $request->category;

            // Update ref_number if category is gcash
            if ($request->category === 'gcash') {
                $payment->ref_number = $request->ref_number;
            } else {
                // Clear ref_number if category is cash
                $payment->ref_number = null;
            }

            $payment->payment_status = 'approved';
            $payment->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment request approved successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function declinePayment(Request $request, $id)
    {
        try {
            $payment = attendance_payments::where('id', $id)
                ->where('status', 'active')
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }

            $payment->payment_status = 'declined';
            $payment->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment request declined successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to decline payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addWaiver(Request $request, $id)
    {
        try {
            $request->validate([
                'waiver_reason' => 'required|string|max:1000',
                'waiver_amount' => 'nullable|numeric|min:0'
            ]);

            $payment = attendance_payments::where('id', $id)
                ->where('status', 'active')
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }

            $payment->waiver_reason = $request->waiver_reason;
            
            // Store original amount before waiver (in case there was a previous waiver)
            $originalAmountBeforeWaiver = ($payment->amount_paid ?? 0) + ($payment->waiver_amount ?? 0);
            
            // Calculate waiver amount
            $waiverAmount = 0;
            if ($request->waiver_amount !== null && $request->waiver_amount > 0) {
                // Use the original amount (before any previous waiver) to calculate
                $waiverAmount = min($request->waiver_amount, $originalAmountBeforeWaiver); // Don't exceed original amount
            } else {
                // Waive full amount
                $waiverAmount = $originalAmountBeforeWaiver;
            }

            // Subtract waiver amount from original amount
            $payment->waiver_amount = $waiverAmount;
            $payment->amount_paid = max(0, $originalAmountBeforeWaiver - $waiverAmount); // Ensure it doesn't go below 0

            // If waived amount equals or exceeds the original payment amount, mark as approved
            if ($waiverAmount >= $originalAmountBeforeWaiver) {
                $payment->payment_status = 'approved';
            }

            $payment->save();

            return response()->json([
                'success' => true,
                'message' => 'Waiver added successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add waiver: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePaymentAmount(Request $request, $id)
    {
        try {
            $request->validate([
                'amount_paid' => 'required|numeric|min:0'
            ]);

            $payment = attendance_payments::where('id', $id)
                ->where('status', 'active')
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }

            $payment->amount_paid = $request->amount_paid;
            $payment->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment amount updated successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment amount: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createPayment(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required',
                'event_id' => 'required',
                'amount_paid' => 'nullable|numeric|min:0'
            ]);

            // Check if payment already exists
            $existingPayment = attendance_payments::where('students_id', $request->student_id)
                ->where('events_id', $request->event_id)
                ->where('status', 'active')
                ->first();

            if ($existingPayment) {
                return response()->json([
                    'success' => true,
                    'payment_id' => $existingPayment->id,
                    'message' => 'Payment record already exists'
                ]);
            }

            // Create new payment record
            $amountPaid = $request->amount_paid ?? 0;
            $payment = attendance_payments::create([
                'students_id' => $request->student_id,
                'events_id' => $request->event_id,
                'amount_paid' => $amountPaid,
                'payment_status' => $amountPaid > 0 ? 'pending' : 'approved', // Auto-approve 0 amount payments
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'message' => 'Payment record created successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment record: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateReceipt(Request $request, $id)
    {
        try {
            $payment = attendance_payments::where('id', $id)
                ->where('status', 'active')
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }

            // Auto-approve if amount is 0 (no fines) so receipt can be generated
            if (($payment->amount_paid ?? 0) == 0 && $payment->payment_status !== 'approved') {
                $payment->payment_status = 'approved';
                $payment->save();
            }

            // Check if already approved (or was just auto-approved)
            if ($payment->payment_status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment must be approved before generating receipt'
                ], 400);
            }

            // Check if receipt already exists
            $existingReceipt = generated_receipt::where('attendance_payments_id', $id)
                ->where('status', 'active')
                ->first();

            if ($existingReceipt) {
                // Return existing receipt data so it can be displayed
                $payment = attendance_payments::with([
                    'students.college',
                    'students.program',
                    'students.organization',
                    'events'
                ])
                ->where('id', $id)
                ->first();

                $student = $payment->students;
                $event = $payment->events;
                
                $timeSchedules = attendance_payments_time_schedule::where('attendance_payments_id', $id)
                    ->where('status', 'active')
                    ->orderBy('log_time', 'asc')
                    ->get();

                $originalAmount = ($payment->amount_paid ?? 0) + ($payment->waiver_amount ?? 0);

                return response()->json([
                    'success' => true,
                    'message' => 'Receipt already exists',
                    'receipt' => [
                        'id' => $existingReceipt->id,
                        'official_receipts' => $existingReceipt->official_receipts,
                        'created_at' => $existingReceipt->created_at ? $existingReceipt->created_at->format('M d, Y') : date('M d, Y'),
                    ],
                    'payment' => [
                        'amount_paid' => $payment->amount_paid ?? 0,
                        'waiver_amount' => $payment->waiver_amount ?? 0,
                        'original_amount' => $originalAmount,
                        'waiver_reason' => $payment->waiver_reason ?? null,
                        'category' => $payment->category ?? null,
                        'ref_number' => $payment->ref_number ?? null,
                    ],
                    'student' => [
                        'student_name' => $student ? $student->student_name : 'N/A',
                        'id_number' => $student ? $student->id_number : 'N/A',
                        'college' => $student && $student->college ? $student->college->college_name : 'N/A',
                        'program' => $student && $student->program ? $student->program->program_name : 'N/A',
                    ],
                    'event' => [
                        'event_name' => $event ? $event->event_name : 'N/A',
                    ],
                    'time_schedules' => $timeSchedules->map(function($schedule) {
                        return [
                            'type_of_schedule_pay' => $schedule->type_of_schedule_pay,
                            'log_time' => $schedule->log_time,
                            'workstate' => $schedule->workstate,
                        ];
                    })->toArray(),
                ]);
            }

            // Generate receipt number (format: OR-YYYYMMDD-XXXX)
            $currentYear = date('Y');
            $currentMonth = date('m');
            $currentDay = date('d');
            
            // Get the last receipt number for today or generate new one
            $lastReceipt = generated_receipt::where('official_receipts', 'like', "OR-{$currentYear}{$currentMonth}{$currentDay}-%")
                ->orderBy('official_receipts', 'desc')
                ->first();

            $receiptNumber = 1;
            if ($lastReceipt) {
                // Extract the number part from the last receipt
                $parts = explode('-', $lastReceipt->official_receipts);
                if (count($parts) >= 3) {
                    $lastNumber = intval($parts[2]);
                    $receiptNumber = $lastNumber + 1;
                }
            }

            // Format receipt number: OR-YYYYMMDD-XXXX (4 digits)
            $officialReceipt = sprintf('OR-%s%s%s-%04d', $currentYear, $currentMonth, $currentDay, $receiptNumber);

            // Create receipt
            $receipt = generated_receipt::create([
                'attendance_payments_id' => $id,
                'official_receipts' => $officialReceipt,
                'status' => 'active',
            ]);

            // Get payment with relationships for receipt display
            $payment = attendance_payments::with([
                'students.college',
                'students.program',
                'students.organization',
                'events'
            ])
            ->where('id', $id)
            ->first();

            $student = $payment->students;
            $event = $payment->events;
            
            // Get time schedules for receipt items
            $timeSchedules = attendance_payments_time_schedule::where('attendance_payments_id', $id)
                ->where('status', 'active')
                ->orderBy('log_time', 'asc')
                ->get();

            // Calculate original amount and waiver
            $originalAmount = ($payment->amount_paid ?? 0) + ($payment->waiver_amount ?? 0);

            return response()->json([
                'success' => true,
                'message' => 'Receipt generated successfully',
                'receipt' => [
                    'id' => $receipt->id,
                    'official_receipts' => $receipt->official_receipts,
                    'created_at' => $receipt->created_at ? $receipt->created_at->format('M d, Y') : date('M d, Y'),
                ],
                'payment' => [
                    'amount_paid' => $payment->amount_paid ?? 0,
                    'waiver_amount' => $payment->waiver_amount ?? 0,
                    'original_amount' => $originalAmount,
                    'waiver_reason' => $payment->waiver_reason ?? null,
                ],
                'student' => [
                    'student_name' => $student ? $student->student_name : 'N/A',
                    'id_number' => $student ? $student->id_number : 'N/A',
                    'college' => $student && $student->college ? $student->college->college_name : 'N/A',
                    'program' => $student && $student->program ? $student->program->program_name : 'N/A',
                ],
                'event' => [
                    'event_name' => $event ? $event->event_name : 'N/A',
                ],
                'time_schedules' => $timeSchedules->map(function($schedule) {
                    return [
                        'type_of_schedule_pay' => $schedule->type_of_schedule_pay,
                        'log_time' => $schedule->log_time,
                        'workstate' => $schedule->workstate,
                    ];
                })->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate receipt: ' . $e->getMessage()
            ], 500);
        }
    }
}
