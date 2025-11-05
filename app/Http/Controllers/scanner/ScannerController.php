<?php

namespace App\Http\Controllers\scanner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

                // Check event window and lateness rules
                $event = events::find($eventId);
                $now = now();
                $isWithinEvent = true;
                if ($event) {
                    if (($event->start_datetime && $now->lt(\Carbon\Carbon::parse($event->start_datetime))) ||
                        ($event->end_datetime && $now->gt(\Carbon\Carbon::parse($event->end_datetime)))) {
                        $isWithinEvent = false;
                    }
                }

                // Determine next workstate (0 time-in / 1 time-out) similar to saveAttendance
                $today = now()->format('Y-m-d');
                $lastAttendance = tbl_attendance::where('student_id', $student->id)
                    ->where('event_id', $eventId)
                    ->where('status', 'active')
                    ->whereRaw('DATE(log_time) = ?', [$today])
                    ->orderBy('log_time', 'desc')
                    ->first();
                $workstate = 0;
                if ($lastAttendance) {
                    $lastWorkstate = $lastAttendance->workstate;
                    $workstate = ($lastWorkstate == "0" || $lastWorkstate == 0) ? 1 : 0;
                }

                $lateRule = events_lates_deduction::where('events_id', $eventId)
                    ->orderBy('id', 'desc')
                    ->first();
                $isLate = false;
                $penalty = 0;
                if ($lateRule) {
                    $currentTime = $now->format('H:i:s');
                    if ($workstate == 0 && $lateRule->time_in) {
                        if ($currentTime > $lateRule->time_in) { $isLate = true; }
                    }
                    if ($workstate == 1 && $lateRule->time_out) {
                        if ($currentTime > $lateRule->time_out) { $isLate = true; }
                    }
                    $penalty = (float)($lateRule->late_penalty ?? 0);
                }

                // Save attendance automatically
                $attendanceResult = $this->saveAttendance($student->id, $eventId);
                
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
                            'time_in' => $lateRule->time_in ?? null,
                            'time_out' => $lateRule->time_out ?? null,
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
    private function saveAttendance($studentId, $eventId)
    {
        try {
            // Get logged in user info
            $userId = auth()->id(); // Get logged in user ID
            $user = auth()->user();
            $userName = $user ? ($user->name ?? 'Scanner') : 'Scanner';
            
            // Validate required fields
            if (!$studentId || !$eventId) {
                throw new \Exception('Missing required fields: student_id or event_id');
            }
            
            // Check if student already has attendance today for this event
            // Simple date comparison
            $today = now()->format('Y-m-d');
            
            $lastAttendance = tbl_attendance::where('student_id', $studentId)
                ->where('event_id', $eventId)
                ->where('status', 'active')
                ->whereRaw('DATE(log_time) = ?', [$today])
                ->orderBy('log_time', 'desc')
                ->first();
            
            // Determine workstate: 0 = time in, 1 = time out
            // If no attendance today, it's time in (0)
            // If last attendance is time in (0), this is time out (1)
            // If last attendance is time out (1), create new time in (0)
            $workstate = 0; // Default: time in
            
            if ($lastAttendance) {
                // If last attendance was time in ("0"), this is time out ("1")
                // If last attendance was time out ("1"), create new time in ("0")
                // Handle both string and integer comparisons
                $lastWorkstate = $lastAttendance->workstate;
                $workstate = ($lastWorkstate == "0" || $lastWorkstate == 0) ? 1 : 0;
            }
            
            // Create new attendance record
            $attendance = new tbl_attendance();
            $attendance->event_id = $eventId;
            $attendance->student_id = $studentId;
            $attendance->log_time = now(); // Current date and time
            $attendance->workstate = (string)$workstate; // Convert to string for VARCHAR column: "0" = time in, "1" = time out
            $attendance->status = 'active';
            $attendance->scan_by = $userName;
            $attendance->user_id = $userId ? (string)$userId : null; // Convert to string for VARCHAR column
            
            // Log before save for debugging
            Log::info('Saving attendance', [
                'event_id' => $eventId,
                'student_id' => $studentId,
                'workstate' => $workstate,
                'user_id' => $userId,
                'scan_by' => $userName
            ]);
            
            $saved = $attendance->save();
            
            if (!$saved) {
                throw new \Exception('Failed to save attendance record');
            }
            
            // Refresh to get the saved record with all attributes
            $attendance->refresh();
            
            // Format log_time - handle both Carbon instance and string
            $logTimeFormatted = $attendance->log_time;
            if ($logTimeFormatted instanceof \Carbon\Carbon) {
                $logTimeFormatted = $logTimeFormatted->format('Y-m-d H:i:s');
            } elseif (is_string($logTimeFormatted)) {
                // If already a string, just use it (might already be formatted)
                $logTimeFormatted = date('Y-m-d H:i:s', strtotime($logTimeFormatted));
            }
            
            Log::info('Attendance saved successfully', [
                'attendance_id' => $attendance->id,
                'log_time' => $logTimeFormatted
            ]);
            
            return [
                'success' => true,
                'workstate' => $workstate,
                'workstate_text' => $workstate == 0 ? 'Time In' : 'Time Out',
                'log_time' => $logTimeFormatted,
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
            
            return [
                'success' => false,
                'message' => 'Failed to save attendance: ' . $e->getMessage()
            ];
        }
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
