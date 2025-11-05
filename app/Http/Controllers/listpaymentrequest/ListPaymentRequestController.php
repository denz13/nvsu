<?php

namespace App\Http\Controllers\listpaymentrequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\attendance_payments;
use App\Models\attendance_payments_time_schedule;
use App\Models\generated_receipt;

class ListPaymentRequestController extends Controller
{
    public function listPaymentRequest()
    {
        // Get all payment requests with related data
        $paymentRequests = attendance_payments::with([
            'students.college',
            'students.program',
            'students.organization',
            'events'
        ])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Format payment requests with time schedule details
        $formattedPayments = [];
        foreach ($paymentRequests as $payment) {
            // Get time schedules for this payment
            $timeSchedules = attendance_payments_time_schedule::where('attendance_payments_id', $payment->id)
                ->where('status', 'active')
                ->orderBy('log_time', 'asc')
                ->get();

            // Count time in (workstate = 0) and time out (workstate = 1)
            $timeInCount = $timeSchedules->where('workstate', 0)->count();
            $timeOutCount = $timeSchedules->where('workstate', 1)->count();

            // Get student info
            $student = $payment->students;
            $event = $payment->events;

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

        return view('list_payment_request.list_payment_request', [
            'paymentRequests' => $paymentRequests,
            'formattedPayments' => $formattedPayments,
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
            $payment = attendance_payments::where('id', $id)
                ->where('status', 'active')
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found'
                ], 404);
            }

            $payment->payment_status = 'approved';
            $payment->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment request approved successfully'
            ]);
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

    public function generateReceipt(Request $request, $id)
    {
        try {
            $payment = attendance_payments::where('id', $id)
                ->where('status', 'active')
                ->where('payment_status', 'approved')
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment request not found or not approved'
                ], 404);
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
