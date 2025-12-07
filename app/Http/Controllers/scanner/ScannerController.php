<?php

namespace App\Http\Controllers\scanner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\students;
use App\Models\tbl_attendance;
use App\Models\events;
use App\Models\events_assign_participants;
use App\Models\events_list_of_participants;
use App\Models\events_lates_deduction;

class ScannerController extends Controller
{
    public function scanner()
    {
        // Get active events for selection
        $events = events::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('scanner.scanner', compact('events'));
    }
    
    public function search(Request $request)
    {
        // Log search request for debugging
        Log::info('Scanner search called', [
            'barcode' => $request->barcode,
            'event_id' => $request->event_id,
            'timestamp' => now()->format('Y-m-d H:i:s.u')
        ]);
        
        $request->validate([
            'barcode' => 'required|string',
            'event_id' => 'nullable|exists:events,id'
        ]);
        
        try {
            $eventId = $request->event_id;
            
            // Convert empty string to null
            if ($eventId === '' || $eventId === null) {
                $eventId = null;
            }
            
            // If no event_id provided, get the most recent active event
            if (!$eventId) {
                $event = events::where('status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if (!$event) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No active event found. Please create an active event first.'
                    ], 404);
                }
                
                $eventId = $event->id;
            }
            // Trim and clean the barcode - remove all whitespace, newlines, tabs
            $barcode = trim($request->barcode);
            $barcode = preg_replace('/\s+/', '', $barcode); // Remove all whitespace
            
            // Debug log (you can remove this later)
            Log::info('Scanner search', [
                'original' => $request->barcode,
                'cleaned' => $barcode,
                'length' => strlen($barcode)
            ]);
            
            // Strategy 1: Exact match (case-insensitive)
            $student = students::with(['college', 'program', 'organization'])
                ->whereRaw('LOWER(TRIM(barcode)) = ?', [strtolower(trim($barcode))])
                ->first();
            
            // Strategy 2: If not found, try case-insensitive with LIKE (handles partial matches)
            if (!$student) {
                $student = students::with(['college', 'program', 'organization'])
                    ->whereRaw('LOWER(barcode) LIKE ?', ['%' . strtolower($barcode) . '%'])
                    ->first();
            }
            
            // Strategy 3: Try matching by id_number (if barcode format is ID + name)
            if (!$student) {
                $student = students::with(['college', 'program', 'organization'])
                    ->where('id_number', $barcode)
                    ->first();
            }
            
            // Strategy 4: Try matching ID number at the start of barcode (in case scanned barcode includes name)
            // Format: ID_NUMBER + FIRST_5_CHARS_OF_NAME (e.g., "2024-001JOHN")
            if (!$student) {
                // Extract potential ID number from start of barcode
                // Try to match if barcode starts with an ID number pattern
                $students = students::with(['college', 'program', 'organization'])->get();
                foreach ($students as $s) {
                    if ($s->barcode) {
                        $storedBarcode = preg_replace('/\s+/', '', $s->barcode); // Remove whitespace
                        // Compare case-insensitive
                        if (strtolower($storedBarcode) === strtolower($barcode)) {
                            $student = $s;
                            break;
                        }
                        // Try if scanned barcode starts with stored barcode or vice versa
                        if (stripos($barcode, $storedBarcode) === 0 || stripos($storedBarcode, $barcode) === 0) {
                            $student = $s;
                            break;
                        }
                    }
                }
            }
            
            // Strategy 5: Try matching by reconstructing barcode from id_number + name
            if (!$student) {
                // Get first 5 chars of ID as potential match start
                if (strlen($barcode) > 5) {
                    $potentialId = substr($barcode, 0, strlen($barcode) - 5); // All except last 5 chars
                    $students = students::with(['college', 'program', 'organization'])
                        ->where('id_number', 'LIKE', $potentialId . '%')
                        ->get();
                    
                    foreach ($students as $s) {
                        // Try to reconstruct barcode: id_number + first 5 chars of name
                        $nameFirst5 = substr(preg_replace('/\s+/', '', $s->student_name ?? ''), 0, 5);
                        $reconstructed = $s->id_number . $nameFirst5;
                        $reconstructed = preg_replace('/\s+/', '', $reconstructed); // Remove whitespace
                        
                        if (strtolower($reconstructed) === strtolower($barcode)) {
                            $student = $s;
                            break;
                        }
                    }
                }
            }
            
            if ($student) {
                // Validate that student is assigned as a participant for this event
                $assignmentIds = events_assign_participants::where('events_id', $eventId)
                    ->where('status', 'active')
                    ->pluck('id');
                $isParticipant = events_list_of_participants::whereIn('events_assign_participants_id', $assignmentIds)
                    ->where('students_id', $student->id)
                    ->where('status', 'active')
                    ->exists();

                if (!$isParticipant) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Student is not assigned as a participant for this event.'
                    ], 403);
                }

                // Get event and validate time windows
                $event = events::find($eventId);
                if (!$event) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Event not found.'
                    ], 404);
                }

                // Use server's current time (accurate system time)
                $now = \Carbon\Carbon::now();
                
                // Validate if current time is within event window based on event_schedule_type
                $isWithinEvent = false;
                $eventStartDateTime = null;
                $eventEndDateTime = null;
                $currentTimeWindow = null; // 'morning' or 'afternoon' for whole_day events
                
                if ($event->event_schedule_type === 'whole_day') {
                    // For whole_day, check both morning and afternoon windows
                    $morningStart = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning) : null;
                    $morningEnd = $event->end_datetime_morning ? \Carbon\Carbon::parse($event->end_datetime_morning) : null;
                    $afternoonStart = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon) : null;
                    $afternoonEnd = $event->end_datetime_afternoon ? \Carbon\Carbon::parse($event->end_datetime_afternoon) : null;
                    
                    // Check if we're in morning window
                    if ($morningStart && $morningEnd && $now->between($morningStart, $morningEnd)) {
                        $isWithinEvent = true;
                        $currentTimeWindow = 'morning';
                        $eventStartDateTime = $morningStart;
                        $eventEndDateTime = $morningEnd;
                    }
                    // Check if we're in afternoon window
                    elseif ($afternoonStart && $afternoonEnd && $now->between($afternoonStart, $afternoonEnd)) {
                        $isWithinEvent = true;
                        $currentTimeWindow = 'afternoon';
                        $eventStartDateTime = $afternoonStart;
                        $eventEndDateTime = $afternoonEnd;
                    }
                } elseif ($event->event_schedule_type === 'half_day_morning') {
                    // Check morning datetime fields
                    if ($event->start_datetime_morning && $event->end_datetime_morning) {
                        $eventStartDateTime = \Carbon\Carbon::parse($event->start_datetime_morning);
                        $eventEndDateTime = \Carbon\Carbon::parse($event->end_datetime_morning);
                        $isWithinEvent = $now->between($eventStartDateTime, $eventEndDateTime);
                        $currentTimeWindow = 'morning';
                    }
                } elseif ($event->event_schedule_type === 'half_day_afternoon') {
                    // Check afternoon datetime fields
                    if ($event->start_datetime_afternoon && $event->end_datetime_afternoon) {
                        $eventStartDateTime = \Carbon\Carbon::parse($event->start_datetime_afternoon);
                        $eventEndDateTime = \Carbon\Carbon::parse($event->end_datetime_afternoon);
                        $isWithinEvent = $now->between($eventStartDateTime, $eventEndDateTime);
                        $currentTimeWindow = 'afternoon';
                    }
                }
                
                // Log event window validation
                Log::info('Event window validation', [
                    'event_schedule_type' => $event->event_schedule_type,
                    'current_time' => $now->format('Y-m-d H:i:s'),
                    'current_time_window' => $currentTimeWindow,
                    'event_start' => $eventStartDateTime ? $eventStartDateTime->format('Y-m-d H:i:s') : null,
                    'event_end' => $eventEndDateTime ? $eventEndDateTime->format('Y-m-d H:i:s') : null,
                    'is_within_event' => $isWithinEvent
                ]);
                
                // Get late rule for this event
                $lateRule = events_lates_deduction::where('events_id', $eventId)
                    ->where('status', 'active')
                    ->orderBy('id', 'desc')
                    ->first();

                // Get allowed time windows based on event schedule type and current time window
                $allowedTimeIn = null;
                $allowedTimeOut = null;

                if ($lateRule) {
                    // For whole_day, use current time window to determine which allowed times to use
                    if ($event->event_schedule_type === 'whole_day') {
                        if ($currentTimeWindow === 'morning') {
                            $allowedTimeIn = $lateRule->time_in_morning;
                            $allowedTimeOut = $lateRule->time_out_morning;
                        } elseif ($currentTimeWindow === 'afternoon') {
                            $allowedTimeIn = $lateRule->time_in_afternoon;
                            $allowedTimeOut = $lateRule->time_out_afternoon;
                        }
                    } elseif ($event->event_schedule_type === 'half_day_morning') {
                        $allowedTimeIn = $lateRule->time_in_morning;
                        $allowedTimeOut = $lateRule->time_out_morning;
                    } elseif ($event->event_schedule_type === 'half_day_afternoon') {
                        $allowedTimeIn = $lateRule->time_in_afternoon;
                        $allowedTimeOut = $lateRule->time_out_afternoon;
                    }
                }

                // Check database directly for existing attendance records for this event
                // For whole_day events, check based on current time window (morning or afternoon)
                // For half_day events, check all records
                
                if ($event->event_schedule_type === 'whole_day' && $currentTimeWindow) {
                    // For whole_day, check if time-in/time-out exists for the current time window
                    // We need to check if there's a time-in/time-out within the current time window
                    $windowStart = $eventStartDateTime;
                    $windowEnd = $eventEndDateTime;
                    
                    // Check if time-in exists within current time window
                    $hasTimeIn = tbl_attendance::where('student_id', $student->id)
                        ->where('event_id', $eventId)
                        ->where('status', 'active')
                        ->where(function($query) {
                            $query->where('workstate', '0')
                                  ->orWhere('workstate', 0);
                        })
                        ->whereBetween('log_time', [$windowStart, $windowEnd])
                        ->exists();
                    
                    // Check if time-out exists within current time window
                    $hasTimeOut = tbl_attendance::where('student_id', $student->id)
                        ->where('event_id', $eventId)
                        ->where('status', 'active')
                        ->where(function($query) {
                            $query->where('workstate', '1')
                                  ->orWhere('workstate', 1);
                        })
                        ->whereBetween('log_time', [$windowStart, $windowEnd])
                        ->exists();
                } else {
                    // For half_day events, check all records (no time window filtering needed)
                    $hasTimeIn = tbl_attendance::where('student_id', $student->id)
                        ->where('event_id', $eventId)
                        ->where('status', 'active')
                        ->where(function($query) {
                            $query->where('workstate', '0')
                                  ->orWhere('workstate', 0);
                        })
                        ->exists();
                    
                    $hasTimeOut = tbl_attendance::where('student_id', $student->id)
                        ->where('event_id', $eventId)
                        ->where('status', 'active')
                        ->where(function($query) {
                            $query->where('workstate', '1')
                                  ->orWhere('workstate', 1);
                        })
                        ->exists();
                }

                // Get all attendance records for logging
                $eventAttendances = tbl_attendance::where('student_id', $student->id)
                    ->where('event_id', $eventId)
                    ->where('status', 'active')
                    ->orderBy('log_time', 'asc')
                    ->get();

                // Log for debugging
                Log::info('Database attendance check', [
                    'student_id' => $student->id,
                    'event_id' => $eventId,
                    'event_schedule_type' => $event->event_schedule_type,
                    'hasTimeIn' => $hasTimeIn,
                    'hasTimeOut' => $hasTimeOut,
                    'attendance_count' => $eventAttendances->count(),
                    'allowedTimeIn' => $allowedTimeIn,
                    'allowedTimeOut' => $allowedTimeOut,
                    'existing_records' => $eventAttendances->map(function($att) {
                        return [
                            'id' => $att->id,
                            'workstate' => $att->workstate,
                            'log_time' => $att->log_time ? $att->log_time->format('Y-m-d H:i:s') : null,
                        ];
                    })->toArray()
                ]);

                // Determine workstate based on database check
                // Rule: If no time-in exists, create time-in first. Only create time-out if time-in exists.
                // For whole_day events: Allow 4 scans total (Time In Morning, Time Out Morning, Time In Afternoon, Time Out Afternoon)
                $workstate = 0; // Default: time in
                
                if ($hasTimeIn && !$hasTimeOut) {
                    // Time-in exists but time-out doesn't in current time window - create time-out
                    $workstate = 1;
                } elseif (!$hasTimeIn) {
                    // No time-in exists in current time window - create time-in first
                    $workstate = 0;
                } elseif ($hasTimeIn && $hasTimeOut) {
                    // Both already exist in current time window - prevent duplicate
                    $windowText = '';
                    if ($event->event_schedule_type === 'whole_day' && $currentTimeWindow) {
                        $windowText = " for {$currentTimeWindow}";
                    }
                    return response()->json([
                        'success' => false,
                        'message' => "You have already completed both Time In and Time Out{$windowText} for this event."
                    ], 400);
                }
                
                // For whole_day events, verify we can still scan in the other time window
                if ($event->event_schedule_type === 'whole_day') {
                    $totalRecords = $eventAttendances->count();
                    if ($totalRecords >= 4) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You have already completed all 4 attendance records (Time In Morning, Time Out Morning, Time In Afternoon, Time Out Afternoon) for this whole day event.'
                        ], 400);
                    }
                }
                
                Log::info('Determined workstate from database', [
                    'workstate' => $workstate,
                    'workstate_text' => $workstate == 0 ? 'Time In' : 'Time Out',
                    'hasTimeIn' => $hasTimeIn,
                    'hasTimeOut' => $hasTimeOut,
                    'current_time_window' => $currentTimeWindow,
                    'total_records' => $eventAttendances->count(),
                    'reason' => !$hasTimeIn ? 'no_time_in_exists' : ($hasTimeOut ? 'both_exist_in_window' : 'time_in_exists_no_time_out')
                ]);

                // Check for lateness (allowed time is only used for penalty calculation, not blocking)
                $isLate = false;
                $penalty = 0;
                if ($lateRule) {
                    $currentTime = $now->format('H:i:s');
                    if ($workstate == 0 && $allowedTimeIn) {
                        $allowedTimeInStr = strlen($allowedTimeIn) == 5 ? $allowedTimeIn . ':00' : $allowedTimeIn;
                        if ($currentTime > $allowedTimeInStr) {
                            $isLate = true;
                        }
                    }
                    if ($workstate == 1 && $allowedTimeOut) {
                        $allowedTimeOutStr = strlen($allowedTimeOut) == 5 ? $allowedTimeOut . ':00' : $allowedTimeOut;
                        if ($currentTime > $allowedTimeOutStr) {
                            $isLate = true;
                        }
                    }
                    $penalty = (float)($lateRule->late_penalty ?? 0);
                }

                // Log before saving attendance
                Log::info('Before saving attendance', [
                    'student_id' => $student->id,
                    'event_id' => $eventId,
                    'workstate' => $workstate,
                    'existing_count' => tbl_attendance::where('student_id', $student->id)
                        ->where('event_id', $eventId)
                        ->where('status', 'active')
                        ->count()
                ]);
                
                // Save attendance automatically - pass event info for whole_day time window checking
                $attendanceResult = $this->saveAttendance($student->id, $eventId, $workstate, $event, $currentTimeWindow, $eventStartDateTime, $eventEndDateTime);
                
                // Convert student to array to avoid serialization issues
                $studentArray = $student->toArray();
                
                return response()->json([
                    'success' => true,
                    'student' => $studentArray,
                    'attendance' => $attendanceResult,
                    'participant_check' => [
                        'is_participant' => true,
                        'is_within_event' => $isWithinEvent,
                        'late' => [
                            'is_late' => $isLate,
                            'allowed_time_in' => $allowedTimeIn ?? null,
                            'allowed_time_out' => $allowedTimeOut ?? null,
                            'penalty' => $penalty,
                            'workstate' => $workstate,
                        ]
                    ],
                    'debug' => [
                        'scanned_barcode' => $barcode,
                        'stored_barcode' => $student->barcode,
                        'match_type' => 'found'
                    ]
                ], 200);
            } else {
                // Return list of sample barcodes for debugging
                $sampleStudents = students::whereNotNull('barcode')
                    ->where('barcode', '!=', '')
                    ->limit(5)
                    ->get(['id_number', 'student_name', 'barcode']);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found with barcode: ' . $barcode,
                    'debug' => [
                        'scanned_barcode' => $barcode,
                        'scanned_length' => strlen($barcode),
                        'sample_barcodes' => $sampleStudents->map(function($s) {
                            return [
                                'id_number' => $s->id_number,
                                'name' => $s->student_name,
                                'barcode' => $s->barcode,
                                'barcode_length' => strlen($s->barcode ?? '')
                            ];
                        })
                    ]
                ], 404);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            Log::error('Scanner validation error', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', $e->errors()['barcode'] ?? []),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Scanner search error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request' => [
                    'barcode' => $request->barcode ?? null,
                    'event_id' => $request->event_id ?? null
                ]
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to search student: ' . $e->getMessage(),
                'error_details' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : []
            ], 500);
        }
    }
    
    /**
     * Save attendance for scanned student
     */
    private function saveAttendance($studentId, $eventId, $workstate = null, $event = null, $currentTimeWindow = null, $eventStartDateTime = null, $eventEndDateTime = null)
    {
        // Log entry to track if method is called multiple times
        Log::info('saveAttendance called', [
            'student_id' => $studentId,
            'event_id' => $eventId,
            'workstate' => $workstate,
            'timestamp' => now()->format('Y-m-d H:i:s.u'),
            'memory_usage' => memory_get_usage(true)
        ]);
        
        // Use database transaction with locking to prevent race conditions
        return DB::transaction(function () use ($studentId, $eventId, $workstate, $event, $currentTimeWindow, $eventStartDateTime, $eventEndDateTime) {
            try {
                // Get logged in user info
                $userId = auth()->id(); // Get logged in user ID
                $user = auth()->user();
                $userName = $user ? ($user->name ?? 'Scanner') : 'Scanner';
                
                // Validate required fields
                if (!$studentId || !$eventId) {
                    throw new \Exception('Missing required fields: student_id or event_id');
                }
                
                // Use server's current time (accurate system time)
                $now = \Carbon\Carbon::now();
                
                // If workstate is not provided, determine it (fallback - should not happen)
                if ($workstate === null) {
                    $eventAttendances = tbl_attendance::where('student_id', $studentId)
                        ->where('event_id', $eventId)
                        ->where('status', 'active')
                        ->lockForUpdate() // Lock rows to prevent concurrent access
                        ->get();
                    
                    $hasTimeIn = $eventAttendances->filter(function($att) {
                        $ws = $att->workstate;
                        return $ws === "0" || $ws === 0;
                    })->count() > 0;
                    
                    $hasTimeOut = $eventAttendances->filter(function($att) {
                        $ws = $att->workstate;
                        return $ws === "1" || $ws === 1;
                    })->count() > 0;
                    
                    // Default to time in (0) if no records exist for this event
                    $workstate = 0;
                    // Only set to time out if time-in exists and time-out doesn't
                    if ($hasTimeIn && !$hasTimeOut) {
                        $workstate = 1;
                    }
                    
                    Log::warning('Workstate was null, determined in saveAttendance', [
                        'student_id' => $studentId,
                        'event_id' => $eventId,
                        'determined_workstate' => $workstate,
                        'hasTimeIn' => $hasTimeIn,
                        'hasTimeOut' => $hasTimeOut
                    ]);
                }
                
                // Double-check: Prevent duplicate time-in or time-out for this event
                // Use lockForUpdate to prevent concurrent saves
                $query = tbl_attendance::where('student_id', $studentId)
                    ->where('event_id', $eventId)
                    ->where('status', 'active')
                    ->lockForUpdate(); // Lock rows to prevent race conditions
                
                // For whole_day events, check within the current time window
                if ($event && $event->event_schedule_type === 'whole_day' && $currentTimeWindow && $eventStartDateTime && $eventEndDateTime) {
                    $query->whereBetween('log_time', [$eventStartDateTime, $eventEndDateTime]);
                }
                
                $existingAttendances = $query->get();
                
                // Check for duplicate using same filter logic
                $hasDuplicate = false;
                if ($workstate == 0) {
                    // Check for time-in
                    $hasDuplicate = $existingAttendances->filter(function($att) {
                        $ws = $att->workstate;
                        return $ws === "0" || $ws === 0;
                    })->count() > 0;
                } else {
                    // Check for time-out
                    $hasDuplicate = $existingAttendances->filter(function($att) {
                        $ws = $att->workstate;
                        return $ws === "1" || $ws === 1;
                    })->count() > 0;
                }
                
                if ($hasDuplicate) {
                    $workstateText = $workstate == 0 ? 'Time In' : 'Time Out';
                    $windowText = $currentTimeWindow ? " ({$currentTimeWindow})" : "";
                    throw new \Exception("You have already recorded {$workstateText}{$windowText} for this event.");
                }
                
                // Additional safety check: Prevent saving if both time-in and time-out exist in same transaction
                // This prevents race conditions where both might be created simultaneously
                $allRecords = tbl_attendance::where('student_id', $studentId)
                    ->where('event_id', $eventId)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->get();
                
                $timeInCount = $allRecords->filter(function($att) {
                    $ws = $att->workstate;
                    return $ws === "0" || $ws === 0;
                })->count();
                
                $timeOutCount = $allRecords->filter(function($att) {
                    $ws = $att->workstate;
                    return $ws === "1" || $ws === 1;
                })->count();
                
                // If we're trying to save time-in but time-in already exists, or time-out but time-out already exists
                if (($workstate == 0 && $timeInCount > 0) || ($workstate == 1 && $timeOutCount > 0)) {
                    $workstateText = $workstate == 0 ? 'Time In' : 'Time Out';
                    throw new \Exception("Duplicate detected: {$workstateText} already exists for this event.");
                }
                
                // Log before save for debugging
                $existingBefore = tbl_attendance::where('student_id', $studentId)
                    ->where('event_id', $eventId)
                    ->where('status', 'active')
                    ->count();
                    
                Log::info('Saving attendance', [
                    'event_id' => $eventId,
                    'student_id' => $studentId,
                    'workstate' => $workstate,
                    'user_id' => $userId,
                    'scan_by' => $userName,
                    'existing_before' => $existingBefore
                ]);
                
                // Final check right before saving - prevent last-second duplicates
                $finalCheckQuery = tbl_attendance::where('student_id', $studentId)
                    ->where('event_id', $eventId)
                    ->where('status', 'active')
                    ->where('workstate', (string)$workstate)
                    ->lockForUpdate();
                
                // For whole_day, also check time window
                if ($event && $event->event_schedule_type === 'whole_day' && $currentTimeWindow && $eventStartDateTime && $eventEndDateTime) {
                    $finalCheckQuery->whereBetween('log_time', [$eventStartDateTime, $eventEndDateTime]);
                }
                
                $finalCheck = $finalCheckQuery->exists();
                
                if ($finalCheck) {
                    $workstateText = $workstate == 0 ? 'Time In' : 'Time Out';
                    throw new \Exception("Final check failed: {$workstateText} already exists. Duplicate prevented.");
                }
                
                // Create new attendance record with accurate server time
                $attendance = new tbl_attendance();
                $attendance->event_id = $eventId;
                $attendance->student_id = $studentId;
                $attendance->log_time = $now; // Use Carbon instance for accurate time
                $attendance->workstate = (string)$workstate; // Convert to string for VARCHAR column: "0" = time in, "1" = time out
                $attendance->status = 'active';
                $attendance->scan_by = $userName;
                $attendance->user_id = $userId ? (string)$userId : null; // Convert to string for VARCHAR column
                
                $saved = $attendance->save();
                
                // Verify only one record was created with this exact timestamp and workstate
                $verifyQuery = tbl_attendance::where('student_id', $studentId)
                    ->where('event_id', $eventId)
                    ->where('status', 'active')
                    ->where('workstate', (string)$workstate)
                    ->where('log_time', '>=', $now->copy()->subSeconds(1))
                    ->where('log_time', '<=', $now->copy()->addSeconds(1));
                
                $verifyCount = $verifyQuery->count();
                
                if ($verifyCount > 1) {
                    Log::error('CRITICAL: Multiple records created in same second!', [
                        'student_id' => $studentId,
                        'event_id' => $eventId,
                        'workstate' => $workstate,
                        'count' => $verifyCount,
                        'timestamp' => $now->format('Y-m-d H:i:s')
                    ]);
                }
                
                // Log after save for debugging
                $existingAfter = tbl_attendance::where('student_id', $studentId)
                    ->where('event_id', $eventId)
                    ->where('status', 'active')
                    ->count();
                    
                Log::info('Attendance saved', [
                    'saved' => $saved,
                    'attendance_id' => $attendance->id ?? null,
                    'existing_after' => $existingAfter,
                    'records_created' => $existingAfter - $existingBefore
                ]);
                
                if (!$saved) {
                    throw new \Exception('Failed to save attendance record');
                }
                
                // Refresh to get the saved record with all attributes
                $attendance->refresh();
                
                // Format log_time - use the server time we saved for accuracy
                $logTimeFormatted = $now->format('Y-m-d H:i:s');
                
                // Also log the database stored time for comparison
                $dbLogTime = $attendance->log_time;
                if ($dbLogTime instanceof \Carbon\Carbon) {
                    $dbLogTimeFormatted = $dbLogTime->format('Y-m-d H:i:s');
                } else {
                    $dbLogTimeFormatted = is_string($dbLogTime) ? $dbLogTime : date('Y-m-d H:i:s', strtotime($dbLogTime));
                }
                
                Log::info('Attendance saved successfully', [
                    'attendance_id' => $attendance->id,
                    'server_time' => $logTimeFormatted,
                    'db_time' => $dbLogTimeFormatted
                ]);
                
                return [
                    'success' => true,
                    'workstate' => $workstate,
                    'workstate_text' => $workstate == 0 ? 'Time In' : 'Time Out',
                    'log_time' => $logTimeFormatted, // Return accurate server time
                    'message' => $workstate == 0 ? 'Time In recorded successfully!' : 'Time Out recorded successfully!'
                ];
            } catch (\Exception $e) {
                Log::error('Attendance save error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'student_id' => $studentId,
                    'event_id' => $eventId,
                    'user_id' => auth()->id()
                ]);
                
                throw $e; // Re-throw to let transaction handle rollback
            }
        });
    }
    
    public function details($id)
    {
        try {
            $student = students::with(['college', 'program', 'organization'])
                ->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'student' => $student
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get student details: ' . $e->getMessage()
            ], 500);
        }
    }
}
