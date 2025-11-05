<?php

namespace App\Http\Controllers\myattendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\tbl_attendance;
use App\Models\events;
use App\Models\events_list_of_participants;
use App\Models\events_lates_deduction;
use App\Models\attendance_payments;
use App\Models\attendance_payments_time_schedule;
use App\Models\generated_receipt;

class MyAttendanceController extends Controller
{
    public function myAttendance()
    {
        // Only students can access this page
        if (!auth('students')->check()) {
            return redirect('/');
        }

        // Get all events for filtering
        $events = events::orderBy('created_at', 'desc')->get();
        
        // Determine current authenticated account (student or web user)
        $currentStudent = auth('students')->check() ? auth('students')->user() : null;
        $currentUser = auth('web')->check() ? auth('web')->user() : null;
        $studentIdFilter = $currentStudent ? $currentStudent->id : null;
        $userIdFilter = $currentUser ? (string)$currentUser->id : null;

        // Get attendance records grouped by student and event
        $attendancesQuery = tbl_attendance::with(['student', 'event', 'user'])
            ->where('status', 'active')
            ->orderBy('log_time', 'desc');

        if (!is_null($studentIdFilter)) {
            $attendancesQuery->where('student_id', $studentIdFilter);
        } elseif (!is_null($userIdFilter)) {
            $attendancesQuery->where('user_id', $userIdFilter);
        }

        $attendances = $attendancesQuery->get();
        
        // Group by event_id only (one card per event) - combine time in and time out
        $groupedAttendances = [];
        
        foreach ($attendances as $attendance) {
            $key = $attendance->event_id; // Group by event_id only
            
            if (!isset($groupedAttendances[$key])) {
                // Initialize grouped attendance per event
                $groupedAttendances[$key] = [
                    'event_id' => $attendance->event_id,
                    'event_name' => $attendance->event ? $attendance->event->event_name : 'N/A',
                    'student_id' => $attendance->student_id,
                    'student_name' => $attendance->student ? $attendance->student->student_name : 'N/A',
                    'student_id_number' => $attendance->student ? $attendance->student->id_number : 'N/A',
                    'student_photo' => $attendance->student ? $attendance->student->photo : null,
                    'time_in' => null,
                    'time_in_formatted' => null,
                    'time_out' => null,
                    'time_out_formatted' => null,
                    'latest_time' => null,
                    'latest_time_formatted' => null,
                    'status' => null,
                    'absence_fine' => 0,
                    'late_penalty' => 0,
                    'total_penalty' => 0,
                    'attendance_count' => 0, // Count of attendance records for this event
                ];
            }
            
            // Increment attendance count
            $groupedAttendances[$key]['attendance_count']++;
            
            // Store time in or time out (keep earliest time_in, latest time_out)
            if ($attendance->workstate == "0" || $attendance->workstate == 0) {
                // Time In - keep earliest
                if (!$groupedAttendances[$key]['time_in']) {
                    $groupedAttendances[$key]['time_in'] = $attendance->log_time ? $attendance->log_time->format('Y-m-d H:i:s') : null;
                    $groupedAttendances[$key]['time_in_formatted'] = $attendance->log_time ? $attendance->log_time->format('M d, Y h:i A') : null;
                } else {
                    $existingTimeInCarbon = \Carbon\Carbon::parse($groupedAttendances[$key]['time_in']);
                    if ($attendance->log_time && $attendance->log_time->lt($existingTimeInCarbon)) {
                        $groupedAttendances[$key]['time_in'] = $attendance->log_time->format('Y-m-d H:i:s');
                        $groupedAttendances[$key]['time_in_formatted'] = $attendance->log_time->format('M d, Y h:i A');
                    }
                }
            } else {
                // Time Out - keep latest
                if (!$groupedAttendances[$key]['time_out']) {
                    $groupedAttendances[$key]['time_out'] = $attendance->log_time ? $attendance->log_time->format('Y-m-d H:i:s') : null;
                    $groupedAttendances[$key]['time_out_formatted'] = $attendance->log_time ? $attendance->log_time->format('M d, Y h:i A') : null;
                } else {
                    $existingTimeOutCarbon = \Carbon\Carbon::parse($groupedAttendances[$key]['time_out']);
                    if ($attendance->log_time && $attendance->log_time->gt($existingTimeOutCarbon)) {
                        $groupedAttendances[$key]['time_out'] = $attendance->log_time->format('Y-m-d H:i:s');
                        $groupedAttendances[$key]['time_out_formatted'] = $attendance->log_time->format('M d, Y h:i A');
                    }
                }
            }
            
            // Update latest time (most recent activity)
            if (!$groupedAttendances[$key]['latest_time']) {
                $groupedAttendances[$key]['latest_time'] = $attendance->log_time ? $attendance->log_time->format('Y-m-d H:i:s') : null;
                $groupedAttendances[$key]['latest_time_formatted'] = $attendance->log_time ? $attendance->log_time->format('M d, Y h:i A') : null;
                $groupedAttendances[$key]['status'] = $attendance->workstate == "0" || $attendance->workstate == 0 ? 'Time In' : 'Time Out';
            } else {
                $existingLatestTimeCarbon = \Carbon\Carbon::parse($groupedAttendances[$key]['latest_time']);
                if ($attendance->log_time && $attendance->log_time->gt($existingLatestTimeCarbon)) {
                    $groupedAttendances[$key]['latest_time'] = $attendance->log_time->format('Y-m-d H:i:s');
                    $groupedAttendances[$key]['latest_time_formatted'] = $attendance->log_time->format('M d, Y h:i A');
                    $groupedAttendances[$key]['status'] = $attendance->workstate == "0" || $attendance->workstate == 0 ? 'Time In' : 'Time Out';
                }
            }
        }
        
        // Convert to collection and sort by latest time
        $formattedAttendances = collect($groupedAttendances)->values()->sortByDesc('latest_time')->values();
        
        // Find events where student is participant but has no attendance (absences)
        $studentId = $studentIdFilter;
        if ($studentId) {
            $participantEvents = events_list_of_participants::whereHas('events_assign_participants.events', function($q) {
                    $q->where('status', 'active');
                })
                ->where('students_id', $studentId)
                ->where('status', 'active')
                ->with(['events_assign_participants.events'])
                ->get();
            
            foreach ($participantEvents as $participant) {
                $event = $participant->events_assign_participants->events ?? null;
                if (!$event) continue;
                
                $eventKey = $event->id; // Group by event_id only
                
                // Check if already in groupedAttendances
                $hasAttendance = $formattedAttendances->first(function($item) use ($eventKey) {
                    return $item['event_id'] == $eventKey;
                });
                
                if (!$hasAttendance) {
                    // Student is participant but has no attendance - mark as absent
                    $lateRule = events_lates_deduction::where('events_id', $event->id)
                        ->where('status', 'active')
                        ->orderBy('id', 'desc')
                        ->first();
                    
                    // Calculate absence fine based on event_schedule_type
                    $absenceFine = 0;
                    $latePenalty = 0; // No late penalty if completely absent
                    $scheduleType = $event->event_schedule_type ?? 'whole_day';
                    $eventFine = $event->fines ?? 0;
                    
                    // If completely absent (no attendance records), full fine, no late penalty
                    $absenceFine = $eventFine;
                    
                    // Get latest time based on schedule type
                    $latestTime = null;
                    $latestTimeFormatted = null;
                    if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_morning') {
                        $latestTime = $event->start_datetime_morning;
                        $latestTimeFormatted = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning)->format('M d, Y h:i A') : null;
                    } elseif ($scheduleType === 'half_day_afternoon') {
                        $latestTime = $event->start_datetime_afternoon;
                        $latestTimeFormatted = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon)->format('M d, Y h:i A') : null;
                    }
                    
                    $formattedAttendances->push([
                        'id' => null,
                        'student_id' => $studentId,
                        'event_id' => $event->id,
                        'student_name' => $currentStudent ? $currentStudent->student_name : 'N/A',
                        'student_id_number' => $currentStudent ? $currentStudent->id_number : 'N/A',
                        'student_photo' => $currentStudent ? $currentStudent->photo : null,
                        'event_name' => $event->event_name ?? 'N/A',
                        'time_in' => null,
                        'time_in_formatted' => null,
                        'time_out' => null,
                        'time_out_formatted' => null,
                        'latest_time' => $latestTime,
                        'latest_time_formatted' => $latestTimeFormatted,
                        'status' => 'Absent',
                        'absence_fine' => $absenceFine,
                        'late_penalty' => 0, // No late penalty if completely absent
                        'total_penalty' => $absenceFine, // Total = absence fine only (no late penalty)
                    ]);
                } else {
                    // Has attendance - check for partial absences and calculate late penalties
                    $attendanceItem = $formattedAttendances->first(function($item) use ($eventKey) {
                        return $item['event_id'] == $eventKey;
                    });
                    
                    if ($attendanceItem) {
                        $lateRule = events_lates_deduction::where('events_id', $event->id)
                            ->where('status', 'active')
                            ->orderBy('id', 'desc')
                            ->first();
                        
                        // Get all attendance records for this event to check partial absences
                        $eventAttendances = $attendancesQuery->where('event_id', $event->id)
                            ->where('student_id', $studentId)
                            ->get();
                        
                        $absenceFine = 0;
                        $latePenalty = 0;
                        $scheduleType = $event->event_schedule_type ?? 'whole_day';
                        $eventFine = $event->fines ?? 0;
                        
                        // Check for partial absences (whole day event but absent in one period only)
                        if ($scheduleType === 'whole_day') {
                            $morningStart = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning) : null;
                            $morningEnd = $event->end_datetime_morning ? \Carbon\Carbon::parse($event->end_datetime_morning) : null;
                            $afternoonStart = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon) : null;
                            $afternoonEnd = $event->end_datetime_afternoon ? \Carbon\Carbon::parse($event->end_datetime_afternoon) : null;
                            
                            $hasMorningAttendance = false;
                            $hasAfternoonAttendance = false;
                            
                            // Check morning attendance
                            if ($morningStart && $morningEnd) {
                                $hasMorningAttendance = $eventAttendances->filter(function($att) use ($morningStart, $morningEnd) {
                                    return $att->log_time && 
                                           $att->log_time->gte($morningStart) && 
                                           $att->log_time->lte($morningEnd);
                                })->isNotEmpty();
                            }
                            
                            // Check afternoon attendance
                            if ($afternoonStart && $afternoonEnd) {
                                $hasAfternoonAttendance = $eventAttendances->filter(function($att) use ($afternoonStart, $afternoonEnd) {
                                    return $att->log_time && 
                                           $att->log_time->gte($afternoonStart) && 
                                           $att->log_time->lte($afternoonEnd);
                                })->isNotEmpty();
                            }
                            
                            // Calculate fine based on which periods are absent
                            if (!$hasMorningAttendance && !$hasAfternoonAttendance) {
                                // Absent for both periods - full fine (should not happen since $hasAttendance is true, but just in case)
                                $absenceFine = $eventFine;
                            } elseif (!$hasMorningAttendance || !$hasAfternoonAttendance) {
                                // Absent for only one period - half fine
                                $absenceFine = $eventFine / 2;
                            }
                            // If present for both periods, $absenceFine remains 0
                        }
                        // For half_day_morning or half_day_afternoon, if has attendance, no absence fine
                        
                        // Calculate late penalties
                        // Check each attendance record against its corresponding period's allowed time
                        if ($lateRule && !$eventAttendances->isEmpty()) {
                            // Morning period check
                            if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_morning') {
                                $morningStart = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning) : null;
                                $morningEnd = $event->end_datetime_morning ? \Carbon\Carbon::parse($event->end_datetime_morning) : null;
                                
                                if ($morningStart && $morningEnd && $lateRule->time_in_morning) {
                                    // Parse allowed time in, ensuring proper time format
                                    $timeInMorningStr = $lateRule->time_in_morning;
                                    if (strlen($timeInMorningStr) == 5) {
                                        $timeInMorningStr .= ':00';
                                    }
                                    $allowedTimeIn = \Carbon\Carbon::parse($morningStart->format('Y-m-d') . ' ' . $timeInMorningStr);
                                    
                                    // Check time in records within morning period
                                    $morningTimeIns = $eventAttendances->where('workstate', 0)->filter(function($att) use ($morningStart, $morningEnd) {
                                        return $att->log_time && 
                                               $att->log_time->gte($morningStart) && 
                                               $att->log_time->lte($morningEnd);
                                    })->sortBy('log_time');
                                    
                                    if ($morningTimeIns->isNotEmpty()) {
                                        $firstMorningTimeIn = $morningTimeIns->first();
                                        if ($firstMorningTimeIn->log_time && $firstMorningTimeIn->log_time->gt($allowedTimeIn)) {
                                            $latePenalty += ($lateRule->late_penalty ?? 0);
                                        }
                                    }
                                }
                                
                                if ($morningStart && $morningEnd && $lateRule->time_out_morning) {
                                    // Parse allowed time out, ensuring proper time format
                                    $timeOutMorningStr = $lateRule->time_out_morning;
                                    if (strlen($timeOutMorningStr) == 5) {
                                        $timeOutMorningStr .= ':00';
                                    }
                                    $allowedTimeOut = \Carbon\Carbon::parse($morningStart->format('Y-m-d') . ' ' . $timeOutMorningStr);
                                    
                                    // Check time out records within morning period
                                    $afternoonStart = ($scheduleType === 'whole_day' && $event->start_datetime_afternoon) 
                                        ? \Carbon\Carbon::parse($event->start_datetime_afternoon) 
                                        : null;
                                    
                                    $morningTimeOuts = $eventAttendances->where('workstate', '!=', 0)->filter(function($att) use ($morningStart, $morningEnd, $afternoonStart, $scheduleType) {
                                        if (!$att->log_time) return false;
                                        
                                        $isOnMorningDate = $att->log_time->format('Y-m-d') === $morningStart->format('Y-m-d');
                                        
                                        // Check if time out falls within morning period
                                        $isInMorningPeriod = $att->log_time->gte($morningStart) && $att->log_time->lte($morningEnd);
                                        
                                        // For whole day events, also include time outs before afternoon start
                                        if ($scheduleType === 'whole_day' && $afternoonStart) {
                                            $isBeforeAfternoon = $att->log_time->lt($afternoonStart);
                                            return $isOnMorningDate && ($isInMorningPeriod || $isBeforeAfternoon);
                                        }
                                        
                                        return $isInMorningPeriod;
                                    })->sortByDesc('log_time');
                                    
                                    if ($morningTimeOuts->isNotEmpty()) {
                                        $lastMorningTimeOut = $morningTimeOuts->first();
                                        if ($lastMorningTimeOut->log_time && $lastMorningTimeOut->log_time->gt($allowedTimeOut)) {
                                            $latePenalty += ($lateRule->late_penalty ?? 0);
                                        }
                                    }
                                }
                            }
                            
                            // Afternoon period check
                            if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_afternoon') {
                                $afternoonStart = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon) : null;
                                $afternoonEnd = $event->end_datetime_afternoon ? \Carbon\Carbon::parse($event->end_datetime_afternoon) : null;
                                
                                if ($afternoonStart && $afternoonEnd && $lateRule->time_in_afternoon) {
                                    // Parse allowed time in, ensuring proper time format
                                    $timeInAfternoonStr = $lateRule->time_in_afternoon;
                                    if (strlen($timeInAfternoonStr) == 5) {
                                        $timeInAfternoonStr .= ':00';
                                    }
                                    $allowedTimeIn = \Carbon\Carbon::parse($afternoonStart->format('Y-m-d') . ' ' . $timeInAfternoonStr);
                                    
                                    // Check time in records within afternoon period
                                    $afternoonTimeIns = $eventAttendances->where('workstate', 0)->filter(function($att) use ($afternoonStart, $afternoonEnd) {
                                        return $att->log_time && 
                                               $att->log_time->gte($afternoonStart) && 
                                               $att->log_time->lte($afternoonEnd);
                                    })->sortBy('log_time');
                                    
                                    if ($afternoonTimeIns->isNotEmpty()) {
                                        $firstAfternoonTimeIn = $afternoonTimeIns->first();
                                        if ($firstAfternoonTimeIn->log_time && $firstAfternoonTimeIn->log_time->gt($allowedTimeIn)) {
                                            $latePenalty += ($lateRule->late_penalty ?? 0);
                                        }
                                    }
                                }
                                
                                if ($afternoonStart && $afternoonEnd && $lateRule->time_out_afternoon) {
                                    // Parse allowed time out, ensuring proper time format
                                    $timeOutAfternoonStr = $lateRule->time_out_afternoon;
                                    if (strlen($timeOutAfternoonStr) == 5) {
                                        $timeOutAfternoonStr .= ':00';
                                    }
                                    $allowedTimeOut = \Carbon\Carbon::parse($afternoonStart->format('Y-m-d') . ' ' . $timeOutAfternoonStr);
                                    
                                    // Check time out records within afternoon period
                                    $morningEnd = ($scheduleType === 'whole_day' && $event->end_datetime_morning) 
                                        ? \Carbon\Carbon::parse($event->end_datetime_morning) 
                                        : null;
                                    
                                    $afternoonTimeOuts = $eventAttendances->where('workstate', '!=', 0)->filter(function($att) use ($afternoonStart, $afternoonEnd, $morningEnd, $scheduleType) {
                                        if (!$att->log_time) return false;
                                        
                                        $isOnAfternoonDate = $att->log_time->format('Y-m-d') === $afternoonStart->format('Y-m-d');
                                        
                                        // Check if time out falls within afternoon period
                                        $isInAfternoonPeriod = $att->log_time->gte($afternoonStart) && $att->log_time->lte($afternoonEnd);
                                        
                                        // For whole day events, also include time outs after morning end
                                        if ($scheduleType === 'whole_day' && $morningEnd) {
                                            $isAfterMorning = $att->log_time->gt($morningEnd);
                                            return $isOnAfternoonDate && ($isInAfternoonPeriod || $isAfterMorning);
                                        }
                                        
                                        return $isInAfternoonPeriod;
                                    })->sortByDesc('log_time');
                                    
                                    if ($afternoonTimeOuts->isNotEmpty()) {
                                        $lastAfternoonTimeOut = $afternoonTimeOuts->first();
                                        if ($lastAfternoonTimeOut->log_time && $lastAfternoonTimeOut->log_time->gt($allowedTimeOut)) {
                                            $latePenalty += ($lateRule->late_penalty ?? 0);
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Update the item using collection map method
                        // Total penalty = absence fine + late penalty
                        // If late for both time in AND time out, late_penalty is added twice (once for each violation)
                        $totalPenalty = $absenceFine + $latePenalty;
                        $formattedAttendances = $formattedAttendances->map(function($item) use ($eventKey, $absenceFine, $latePenalty, $totalPenalty) {
                            if ($item['event_id'] == $eventKey) {
                                $item['late_penalty'] = $latePenalty;
                                $item['absence_fine'] = $absenceFine;
                                $item['total_penalty'] = $totalPenalty;
                                $item['status'] = $absenceFine > 0 ? 'Partially Absent' : 'Present';
                            }
                            return $item;
                        });
                    }
                }
            }
        }
        
        // Re-sort after adding absences
        $formattedAttendances = $formattedAttendances->sortByDesc('latest_time')->values();
        
        // Add payment status for each attendance
        if ($studentId) {
            $formattedAttendances = $formattedAttendances->map(function($attendance) use ($studentId) {
                $payment = attendance_payments::where('students_id', $studentId)
                    ->where('events_id', $attendance['event_id'])
                    ->where('status', 'active')
                    ->first();
                
                $attendance['payment_id'] = $payment ? $payment->id : null;
                $attendance['payment_status'] = $payment ? $payment->payment_status : null;
                $attendance['has_receipt'] = false;
                
                if ($payment && $payment->payment_status === 'approved') {
                    $receipt = generated_receipt::where('attendance_payments_id', $payment->id)
                        ->where('status', 'active')
                        ->first();
                    $attendance['has_receipt'] = $receipt ? true : false;
                    $attendance['receipt_id'] = $receipt ? $receipt->id : null;
                }
                
                return $attendance;
            });
        }
        
        return view('my_attendance.my_attendance', compact('events', 'formattedAttendances'));
    }

    public function getAttendanceList(Request $request)
    {
        try {
            // Only students can access the JSON list
            if (!auth('students')->check()) {
                return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            }

            $currentStudent = auth('students')->check() ? auth('students')->user() : null;
            $currentUser = auth('web')->check() ? auth('web')->user() : null;
            $studentIdFilter = $currentStudent ? $currentStudent->id : null;
            $userIdFilter = $currentUser ? (string)$currentUser->id : null;
            $perPage = (int)$request->input('per_page', 10);
            $currentPage = (int)$request->input('page', 1);

            $attendancesQuery = tbl_attendance::with(['student', 'event', 'user'])
                ->where('status', 'active')
                ->orderBy('log_time', 'desc');

            if (!is_null($studentIdFilter)) {
                $attendancesQuery->where('student_id', $studentIdFilter);
            } elseif (!is_null($userIdFilter)) {
                $attendancesQuery->where('user_id', $userIdFilter);
            }

            $attendances = $attendancesQuery->get();

            $groupedAttendances = [];
            foreach ($attendances as $attendance) {
                $key = $attendance->event_id; // Group by event_id only
                if (!isset($groupedAttendances[$key])) {
                    $groupedAttendances[$key] = [
                        'event_id' => $attendance->event_id,
                        'event_name' => $attendance->event ? $attendance->event->event_name : 'N/A',
                        'student_id' => $attendance->student_id,
                        'student_name' => $attendance->student ? $attendance->student->student_name : 'N/A',
                        'student_id_number' => $attendance->student ? $attendance->student->id_number : 'N/A',
                        'student_photo' => $attendance->student ? $attendance->student->photo : null,
                        'time_in' => null,
                        'time_in_formatted' => null,
                        'time_out' => null,
                        'time_out_formatted' => null,
                        'latest_time' => null,
                        'latest_time_formatted' => null,
                        'absence_fine' => 0,
                        'late_penalty' => 0,
                        'total_penalty' => 0,
                        'status' => null,
                    ];
                }

                // Store time in or time out (keep earliest time_in, latest time_out)
                if ($attendance->workstate == '0' || $attendance->workstate == 0) {
                    // Time In - keep earliest
                    if (!$groupedAttendances[$key]['time_in']) {
                        $groupedAttendances[$key]['time_in'] = $attendance->log_time ? $attendance->log_time->format('Y-m-d H:i:s') : null;
                        $groupedAttendances[$key]['time_in_formatted'] = $attendance->log_time ? $attendance->log_time->format('M d, Y h:i A') : null;
                    } else {
                        $existingTimeInCarbon = \Carbon\Carbon::parse($groupedAttendances[$key]['time_in']);
                        if ($attendance->log_time && $attendance->log_time->lt($existingTimeInCarbon)) {
                            $groupedAttendances[$key]['time_in'] = $attendance->log_time->format('Y-m-d H:i:s');
                            $groupedAttendances[$key]['time_in_formatted'] = $attendance->log_time->format('M d, Y h:i A');
                        }
                    }
                } else {
                    // Time Out - keep latest
                    if (!$groupedAttendances[$key]['time_out']) {
                        $groupedAttendances[$key]['time_out'] = $attendance->log_time ? $attendance->log_time->format('Y-m-d H:i:s') : null;
                        $groupedAttendances[$key]['time_out_formatted'] = $attendance->log_time ? $attendance->log_time->format('M d, Y h:i A') : null;
                    } else {
                        $existingTimeOutCarbon = \Carbon\Carbon::parse($groupedAttendances[$key]['time_out']);
                        if ($attendance->log_time && $attendance->log_time->gt($existingTimeOutCarbon)) {
                            $groupedAttendances[$key]['time_out'] = $attendance->log_time->format('Y-m-d H:i:s');
                            $groupedAttendances[$key]['time_out_formatted'] = $attendance->log_time->format('M d, Y h:i A');
                        }
                    }
                }

                // Update latest time (most recent activity)
                $existingLatest = $groupedAttendances[$key]['latest_time'];
                if (!$existingLatest || ($attendance->log_time && \Carbon\Carbon::parse($existingLatest)->lt($attendance->log_time))) {
                    $groupedAttendances[$key]['latest_time'] = $attendance->log_time ? $attendance->log_time->format('Y-m-d H:i:s') : null;
                    $groupedAttendances[$key]['latest_time_formatted'] = $attendance->log_time ? $attendance->log_time->format('M d, Y h:i A') : null;
                }
            }

            $collection = collect($groupedAttendances)->values()->sortByDesc('latest_time')->values();

            // Find events where student is participant but has no attendance (absences)
            $studentId = $studentIdFilter;
            if ($studentId) {
                $participantEvents = events_list_of_participants::whereHas('events_assign_participants.events', function($q) {
                        $q->where('status', 'active');
                    })
                    ->where('students_id', $studentId)
                    ->where('status', 'active')
                    ->with(['events_assign_participants.events'])
                    ->get();
                
                foreach ($participantEvents as $participant) {
                    $event = $participant->events_assign_participants->events ?? null;
                    if (!$event) continue;
                    
                    $eventKey = $event->id; // Group by event_id only
                    
                    // Check if already in collection
                    $hasAttendance = $collection->first(function($item) use ($eventKey) {
                        return $item['event_id'] == $eventKey;
                    });
                    
                    if (!$hasAttendance) {
                        // Student is participant but has no attendance - mark as absent
                        $lateRule = events_lates_deduction::where('events_id', $event->id)
                            ->where('status', 'active')
                            ->orderBy('id', 'desc')
                            ->first();
                        
                        // Calculate absence fine based on event_schedule_type
                        $absenceFine = 0;
                        $latePenalty = 0; // No late penalty if completely absent
                        $scheduleType = $event->event_schedule_type ?? 'whole_day';
                        $eventFine = $event->fines ?? 0;
                        
                        // If completely absent (no attendance records), full fine, no late penalty
                        $absenceFine = $eventFine;
                        
                        // Get latest time based on schedule type
                        $latestTime = null;
                        $latestTimeFormatted = null;
                        if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_morning') {
                            $latestTime = $event->start_datetime_morning;
                            $latestTimeFormatted = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning)->format('M d, Y h:i A') : null;
                        } elseif ($scheduleType === 'half_day_afternoon') {
                            $latestTime = $event->start_datetime_afternoon;
                            $latestTimeFormatted = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon)->format('M d, Y h:i A') : null;
                        }
                        
                        $collection->push([
                            'id' => null,
                            'student_id' => $studentId,
                            'event_id' => $event->id,
                            'student_name' => $currentStudent ? $currentStudent->student_name : 'N/A',
                            'student_id_number' => $currentStudent ? $currentStudent->id_number : 'N/A',
                            'student_photo' => $currentStudent ? $currentStudent->photo : null,
                            'event_name' => $event->event_name ?? 'N/A',
                            'time_in' => null,
                            'time_in_formatted' => null,
                            'time_out' => null,
                            'time_out_formatted' => null,
                            'latest_time' => $latestTime,
                            'latest_time_formatted' => $latestTimeFormatted,
                            'status' => 'Absent',
                            'absence_fine' => $absenceFine,
                            'late_penalty' => 0, // No late penalty if completely absent
                            'total_penalty' => $absenceFine, // Total = absence fine only (no late penalty)
                        ]);
                    } else {
                        // Has attendance - check for partial absences and calculate late penalties
                        $attendanceItem = $collection->first(function($item) use ($eventKey) {
                            return $item['event_id'] == $eventKey;
                        });
                        
                        if ($attendanceItem) {
                            $lateRule = events_lates_deduction::where('events_id', $event->id)
                                ->where('status', 'active')
                                ->orderBy('id', 'desc')
                                ->first();
                            
                            // Get all attendance records for this event to check partial absences
                            $eventAttendances = $attendancesQuery->where('event_id', $event->id)
                                ->where('student_id', $studentId)
                                ->get();
                            
                            $absenceFine = 0;
                            $latePenalty = 0;
                            $scheduleType = $event->event_schedule_type ?? 'whole_day';
                            $eventFine = $event->fines ?? 0;
                            
                            // Check for partial absences (whole day event but absent in one period only)
                            if ($scheduleType === 'whole_day') {
                                $morningStart = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning) : null;
                                $morningEnd = $event->end_datetime_morning ? \Carbon\Carbon::parse($event->end_datetime_morning) : null;
                                $afternoonStart = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon) : null;
                                $afternoonEnd = $event->end_datetime_afternoon ? \Carbon\Carbon::parse($event->end_datetime_afternoon) : null;
                                
                                $hasMorningAttendance = false;
                                $hasAfternoonAttendance = false;
                                
                                // Check morning attendance
                                if ($morningStart && $morningEnd) {
                                    $hasMorningAttendance = $eventAttendances->filter(function($att) use ($morningStart, $morningEnd) {
                                        return $att->log_time && 
                                               $att->log_time->gte($morningStart) && 
                                               $att->log_time->lte($morningEnd);
                                    })->isNotEmpty();
                                }
                                
                                // Check afternoon attendance
                                if ($afternoonStart && $afternoonEnd) {
                                    $hasAfternoonAttendance = $eventAttendances->filter(function($att) use ($afternoonStart, $afternoonEnd) {
                                        return $att->log_time && 
                                               $att->log_time->gte($afternoonStart) && 
                                               $att->log_time->lte($afternoonEnd);
                                    })->isNotEmpty();
                                }
                                
                                // Calculate fine based on which periods are absent
                                if (!$hasMorningAttendance && !$hasAfternoonAttendance) {
                                    // Absent for both periods - full fine (should not happen since $hasAttendance is true, but just in case)
                                    $absenceFine = $eventFine;
                                } elseif (!$hasMorningAttendance || !$hasAfternoonAttendance) {
                                    // Absent for only one period - half fine
                                    $absenceFine = $eventFine / 2;
                                }
                                // If present for both periods, $absenceFine remains 0
                            }
                            // For half_day_morning or half_day_afternoon, if has attendance, no absence fine
                            
                            // Calculate late penalties
                            // Check each attendance record against its corresponding period's allowed time
                            if ($lateRule && !$eventAttendances->isEmpty()) {
                                // Morning period check
                                if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_morning') {
                                    $morningStart = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning) : null;
                                    $morningEnd = $event->end_datetime_morning ? \Carbon\Carbon::parse($event->end_datetime_morning) : null;
                                    
                                    if ($morningStart && $morningEnd && $lateRule->time_in_morning) {
                                        // Parse allowed time in, ensuring proper time format
                                        $timeInMorningStr = $lateRule->time_in_morning;
                                        if (strlen($timeInMorningStr) == 5) {
                                            $timeInMorningStr .= ':00';
                                        }
                                        $allowedTimeIn = \Carbon\Carbon::parse($morningStart->format('Y-m-d') . ' ' . $timeInMorningStr);
                                        
                                        // Check time in records within morning period
                                        $morningTimeIns = $eventAttendances->where('workstate', 0)->filter(function($att) use ($morningStart, $morningEnd) {
                                            return $att->log_time && 
                                                   $att->log_time->gte($morningStart) && 
                                                   $att->log_time->lte($morningEnd);
                                        })->sortBy('log_time');
                                        
                                        if ($morningTimeIns->isNotEmpty()) {
                                            $firstMorningTimeIn = $morningTimeIns->first();
                                            if ($firstMorningTimeIn->log_time && $firstMorningTimeIn->log_time->gt($allowedTimeIn)) {
                                                $latePenalty += ($lateRule->late_penalty ?? 0);
                                            }
                                        }
                                    }
                                    
                                    if ($morningStart && $morningEnd && $lateRule->time_out_morning) {
                                        // Parse allowed time out, ensuring proper time format
                                        $timeOutMorningStr = $lateRule->time_out_morning;
                                        if (strlen($timeOutMorningStr) == 5) {
                                            $timeOutMorningStr .= ':00';
                                        }
                                        $allowedTimeOut = \Carbon\Carbon::parse($morningStart->format('Y-m-d') . ' ' . $timeOutMorningStr);
                                        
                                        // Check time out records within morning period
                                        $afternoonStart = ($scheduleType === 'whole_day' && $event->start_datetime_afternoon) 
                                            ? \Carbon\Carbon::parse($event->start_datetime_afternoon) 
                                            : null;
                                        
                                        $morningTimeOuts = $eventAttendances->where('workstate', '!=', 0)->filter(function($att) use ($morningStart, $morningEnd, $afternoonStart, $scheduleType) {
                                            if (!$att->log_time) return false;
                                            
                                            $isOnMorningDate = $att->log_time->format('Y-m-d') === $morningStart->format('Y-m-d');
                                            
                                            // Check if time out falls within morning period
                                            $isInMorningPeriod = $att->log_time->gte($morningStart) && $att->log_time->lte($morningEnd);
                                            
                                            // For whole day events, also include time outs before afternoon start
                                            if ($scheduleType === 'whole_day' && $afternoonStart) {
                                                $isBeforeAfternoon = $att->log_time->lt($afternoonStart);
                                                return $isOnMorningDate && ($isInMorningPeriod || $isBeforeAfternoon);
                                            }
                                            
                                            return $isInMorningPeriod;
                                        })->sortByDesc('log_time');
                                        
                                        if ($morningTimeOuts->isNotEmpty()) {
                                            $lastMorningTimeOut = $morningTimeOuts->first();
                                            if ($lastMorningTimeOut->log_time && $lastMorningTimeOut->log_time->gt($allowedTimeOut)) {
                                                $latePenalty += ($lateRule->late_penalty ?? 0);
                                            }
                                        }
                                    }
                                }
                                
                                // Afternoon period check
                                if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_afternoon') {
                                    $afternoonStart = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon) : null;
                                    $afternoonEnd = $event->end_datetime_afternoon ? \Carbon\Carbon::parse($event->end_datetime_afternoon) : null;
                                    
                                    if ($afternoonStart && $afternoonEnd && $lateRule->time_in_afternoon) {
                                        // Parse allowed time in, ensuring proper time format
                                        $timeInAfternoonStr = $lateRule->time_in_afternoon;
                                        if (strlen($timeInAfternoonStr) == 5) {
                                            $timeInAfternoonStr .= ':00';
                                        }
                                        $allowedTimeIn = \Carbon\Carbon::parse($afternoonStart->format('Y-m-d') . ' ' . $timeInAfternoonStr);
                                        
                                        // Check time in records within afternoon period
                                        $afternoonTimeIns = $eventAttendances->where('workstate', 0)->filter(function($att) use ($afternoonStart, $afternoonEnd) {
                                            return $att->log_time && 
                                                   $att->log_time->gte($afternoonStart) && 
                                                   $att->log_time->lte($afternoonEnd);
                                        })->sortBy('log_time');
                                        
                                        if ($afternoonTimeIns->isNotEmpty()) {
                                            $firstAfternoonTimeIn = $afternoonTimeIns->first();
                                            if ($firstAfternoonTimeIn->log_time && $firstAfternoonTimeIn->log_time->gt($allowedTimeIn)) {
                                                $latePenalty += ($lateRule->late_penalty ?? 0);
                                            }
                                        }
                                    }
                                    
                                    if ($afternoonStart && $afternoonEnd && $lateRule->time_out_afternoon) {
                                        // Parse allowed time out, ensuring proper time format
                                        $timeOutAfternoonStr = $lateRule->time_out_afternoon;
                                        if (strlen($timeOutAfternoonStr) == 5) {
                                            $timeOutAfternoonStr .= ':00';
                                        }
                                        $allowedTimeOut = \Carbon\Carbon::parse($afternoonStart->format('Y-m-d') . ' ' . $timeOutAfternoonStr);
                                        
                                        // Check time out records within afternoon period
                                        $morningEnd = ($scheduleType === 'whole_day' && $event->end_datetime_morning) 
                                            ? \Carbon\Carbon::parse($event->end_datetime_morning) 
                                            : null;
                                        
                                        $afternoonTimeOuts = $eventAttendances->where('workstate', '!=', 0)->filter(function($att) use ($afternoonStart, $afternoonEnd, $morningEnd, $scheduleType) {
                                            if (!$att->log_time) return false;
                                            
                                            $isOnAfternoonDate = $att->log_time->format('Y-m-d') === $afternoonStart->format('Y-m-d');
                                            
                                            // Check if time out falls within afternoon period
                                            $isInAfternoonPeriod = $att->log_time->gte($afternoonStart) && $att->log_time->lte($afternoonEnd);
                                            
                                            // For whole day events, also include time outs after morning end
                                            if ($scheduleType === 'whole_day' && $morningEnd) {
                                                $isAfterMorning = $att->log_time->gt($morningEnd);
                                                return $isOnAfternoonDate && ($isInAfternoonPeriod || $isAfterMorning);
                                            }
                                            
                                            return $isInAfternoonPeriod;
                                        })->sortByDesc('log_time');
                                        
                                        if ($afternoonTimeOuts->isNotEmpty()) {
                                            $lastAfternoonTimeOut = $afternoonTimeOuts->first();
                                            if ($lastAfternoonTimeOut->log_time && $lastAfternoonTimeOut->log_time->gt($allowedTimeOut)) {
                                                $latePenalty += ($lateRule->late_penalty ?? 0);
                                            }
                                        }
                                    }
                                }
                            }
                            
                            // Find index and update the item
                            // Total penalty = absence fine + late penalty
                            // If late for both time in AND time out, late_penalty is added twice (once for each violation)
                            $totalPenalty = $absenceFine + $latePenalty;
                            $index = $collection->search(function($item) use ($eventKey) {
                                return ($item['student_id'] . '_' . $item['event_id']) == $eventKey;
                            });
                            if ($index !== false) {
                                $collection[$index]['late_penalty'] = $latePenalty;
                                $collection[$index]['absence_fine'] = $absenceFine;
                                $collection[$index]['total_penalty'] = $totalPenalty;
                                $collection[$index]['status'] = $absenceFine > 0 ? 'Partially Absent' : 'Present';
                            }
                        }
                    }
                }
            }
            
            // Re-sort after adding absences
            $collection = $collection->sortByDesc('latest_time')->values();
            
            // Add payment status for each attendance
            if ($studentId) {
                $collection = $collection->map(function($attendance) use ($studentId) {
                    $payment = attendance_payments::where('students_id', $studentId)
                        ->where('events_id', $attendance['event_id'])
                        ->where('status', 'active')
                        ->first();
                    
                    $attendance['payment_id'] = $payment ? $payment->id : null;
                    $attendance['payment_status'] = $payment ? $payment->payment_status : null;
                    $attendance['has_receipt'] = false;
                    
                    if ($payment && $payment->payment_status === 'approved') {
                        $receipt = generated_receipt::where('attendance_payments_id', $payment->id)
                            ->where('status', 'active')
                            ->first();
                        $attendance['has_receipt'] = $receipt ? true : false;
                        $attendance['receipt_id'] = $receipt ? $receipt->id : null;
                    }
                    
                    return $attendance;
                });
            }

            $total = $collection->count();
            $lastPage = (int)ceil($total / max($perPage, 1));
            $offset = ($currentPage - 1) * $perPage;
            $items = $collection->slice($offset, $perPage)->values()->toArray();

            return response()->json([
                'success' => true,
                'attendances' => $items,
                'pagination' => [
                    'current_page' => $currentPage,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => $lastPage,
                    'from' => $total > 0 ? $offset + 1 : 0,
                    'to' => min($offset + $perPage, $total),
                    'base_url' => '/myattendance/list?page=',
                    'has_more' => $currentPage < $lastPage,
                    'on_first_page' => $currentPage <= 1,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    // Get detailed attendance records for a specific event
    public function getEventAttendanceDetails($eventId)
    {
        // Only students can access this
        if (!auth('students')->check()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        
        try {
            $currentStudent = auth('students')->user();
            $studentId = $currentStudent->id;
            
            // Get all attendance records for this student and event
            $attendances = tbl_attendance::with(['student', 'event', 'user'])
                ->where('status', 'active')
                ->where('student_id', $studentId)
                ->where('event_id', $eventId)
                ->orderBy('log_time', 'asc')
                ->get();
            
            // Get event info first
            $event = events::find($eventId);
            
            // Check if participant
            $participant = events_list_of_participants::whereHas('events_assign_participants.events', function($q) use ($eventId) {
                    $q->where('id', $eventId)->where('status', 'active');
                })
                ->where('students_id', $studentId)
                ->where('status', 'active')
                ->first();
            
            $isParticipant = $participant !== null;
            
            // Format attendance records
            $formattedRecords = [];
            foreach ($attendances as $attendance) {
                $formattedRecords[] = [
                    'id' => $attendance->id,
                    'log_time' => $attendance->log_time ? $attendance->log_time->format('Y-m-d H:i:s') : null,
                    'log_time_formatted' => $attendance->log_time ? $attendance->log_time->format('M d, Y h:i A') : null,
                    'workstate' => $attendance->workstate == "0" || $attendance->workstate == 0 ? 'Time In' : 'Time Out',
                    'workstate_code' => $attendance->workstate,
                ];
            }
            
            // Check for absences based on event_schedule_type
            $absentRecords = [];
            $isAbsent = false;
            
            if ($isParticipant && $event) {
                $scheduleType = $event->event_schedule_type ?? 'whole_day';
                $hasMorningAttendance = false;
                $hasAfternoonAttendance = false;
                
                // Check if student has attendance for morning period
                if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_morning') {
                    $morningStart = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning) : null;
                    $morningEnd = $event->end_datetime_morning ? \Carbon\Carbon::parse($event->end_datetime_morning) : null;
                    
                    if ($morningStart && $morningEnd) {
                        $hasMorningAttendance = $attendances->filter(function($att) use ($morningStart, $morningEnd) {
                            return $att->log_time && 
                                   $att->log_time->gte($morningStart) && 
                                   $att->log_time->lte($morningEnd);
                        })->isNotEmpty();
                        
                        if (!$hasMorningAttendance) {
                            $absentRecords[] = [
                                'id' => null,
                                'log_time' => null,
                                'log_time_formatted' => $morningStart->format('M d, Y'),
                                'workstate' => 'Absent (Morning)',
                                'workstate_code' => 'absent_morning',
                            ];
                        }
                    }
                }
                
                // Check if student has attendance for afternoon period
                if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_afternoon') {
                    $afternoonStart = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon) : null;
                    $afternoonEnd = $event->end_datetime_afternoon ? \Carbon\Carbon::parse($event->end_datetime_afternoon) : null;
                    
                    if ($afternoonStart && $afternoonEnd) {
                        $hasAfternoonAttendance = $attendances->filter(function($att) use ($afternoonStart, $afternoonEnd) {
                            return $att->log_time && 
                                   $att->log_time->gte($afternoonStart) && 
                                   $att->log_time->lte($afternoonEnd);
                        })->isNotEmpty();
                        
                        if (!$hasAfternoonAttendance) {
                            $absentRecords[] = [
                                'id' => null,
                                'log_time' => null,
                                'log_time_formatted' => $afternoonStart->format('M d, Y'),
                                'workstate' => 'Absent (Afternoon)',
                                'workstate_code' => 'absent_afternoon',
                            ];
                        }
                    }
                }
                
                // If completely absent (no attendance records at all)
                if ($attendances->isEmpty()) {
                    $isAbsent = true;
                    if ($scheduleType === 'whole_day') {
                        $absentRecords = [[
                            'id' => null,
                            'log_time' => null,
                            'log_time_formatted' => $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning)->format('M d, Y') : 'N/A',
                            'workstate' => 'Absent (Whole Day)',
                            'workstate_code' => 'absent',
                        ]];
                    } elseif ($scheduleType === 'half_day_morning') {
                        $absentRecords = [[
                            'id' => null,
                            'log_time' => null,
                            'log_time_formatted' => $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning)->format('M d, Y') : 'N/A',
                            'workstate' => 'Absent (Morning)',
                            'workstate_code' => 'absent_morning',
                        ]];
                    } elseif ($scheduleType === 'half_day_afternoon') {
                        $absentRecords = [[
                            'id' => null,
                            'log_time' => null,
                            'log_time_formatted' => $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon)->format('M d, Y') : 'N/A',
                            'workstate' => 'Absent (Afternoon)',
                            'workstate_code' => 'absent_afternoon',
                        ]];
                    }
                } else {
                    // Merge absent records with attendance records
                    // Sort all records by date/time
                    $allRecords = array_merge($formattedRecords, $absentRecords);
                    usort($allRecords, function($a, $b) {
                        if ($a['log_time'] && $b['log_time']) {
                            return strcmp($a['log_time'], $b['log_time']);
                        }
                        if (!$a['log_time']) return 1;
                        if (!$b['log_time']) return -1;
                        return 0;
                    });
                    $formattedRecords = $allRecords;
                }
            }
            
            // Get late deduction rules
            $lateRule = events_lates_deduction::where('events_id', $eventId)
                ->where('status', 'active')
                ->orderBy('id', 'desc')
                ->first();
            
            // Format late deduction rules based on schedule type
            $lateDeductionInfo = null;
            if ($lateRule && $event) {
                $scheduleType = $event->event_schedule_type ?? 'whole_day';
                $lateDeductionInfo = [
                    'late_penalty' => $lateRule->late_penalty ?? 0,
                    'schedule_type' => $scheduleType,
                    'morning' => null,
                    'afternoon' => null,
                ];
                
                if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_morning') {
                    $lateDeductionInfo['morning'] = [
                        'time_in' => $lateRule->time_in_morning,
                        'time_out' => $lateRule->time_out_morning,
                    ];
                }
                
                if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_afternoon') {
                    $lateDeductionInfo['afternoon'] = [
                        'time_in' => $lateRule->time_in_afternoon,
                        'time_out' => $lateRule->time_out_afternoon,
                    ];
                }
            }
            
            // Calculate penalties
            $absenceFine = 0;
            $latePenalty = 0;
            $totalPenalty = 0;
            
            // Calculate absence fine based on event_schedule_type and which periods student was absent
            if ($isParticipant && $event) {
                $scheduleType = $event->event_schedule_type ?? 'whole_day';
                $eventFine = $event->fines ?? 0;
                
                // Check if student is completely absent (no attendance records at all)
                if ($attendances->isEmpty()) {
                    $isAbsent = true;
                    // Completely absent - full fine
                    $absenceFine = $eventFine;
                } else {
                    // Check for partial absences (whole day event but absent in one period only)
                    if ($scheduleType === 'whole_day') {
                        $morningStart = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning) : null;
                        $morningEnd = $event->end_datetime_morning ? \Carbon\Carbon::parse($event->end_datetime_morning) : null;
                        $afternoonStart = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon) : null;
                        $afternoonEnd = $event->end_datetime_afternoon ? \Carbon\Carbon::parse($event->end_datetime_afternoon) : null;
                        
                        $hasMorningAttendance = false;
                        $hasAfternoonAttendance = false;
                        
                        // Check morning attendance
                        if ($morningStart && $morningEnd) {
                            $hasMorningAttendance = $attendances->filter(function($att) use ($morningStart, $morningEnd) {
                                return $att->log_time && 
                                       $att->log_time->gte($morningStart) && 
                                       $att->log_time->lte($morningEnd);
                            })->isNotEmpty();
                        }
                        
                        // Check afternoon attendance
                        if ($afternoonStart && $afternoonEnd) {
                            $hasAfternoonAttendance = $attendances->filter(function($att) use ($afternoonStart, $afternoonEnd) {
                                return $att->log_time && 
                                       $att->log_time->gte($afternoonStart) && 
                                       $att->log_time->lte($afternoonEnd);
                            })->isNotEmpty();
                        }
                        
                        // Calculate fine based on which periods are absent
                        if (!$hasMorningAttendance && !$hasAfternoonAttendance) {
                            // Absent for both periods - full fine
                            $absenceFine = $eventFine;
                            $isAbsent = true;
                        } elseif (!$hasMorningAttendance || !$hasAfternoonAttendance) {
                            // Absent for only one period - half fine
                            $absenceFine = $eventFine / 2;
                        }
                        // If present for both periods, $absenceFine remains 0
                    } elseif ($scheduleType === 'half_day_morning') {
                        // Half day morning - if absent, full fine
                        $morningStart = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning) : null;
                        $morningEnd = $event->end_datetime_morning ? \Carbon\Carbon::parse($event->end_datetime_morning) : null;
                        
                        if ($morningStart && $morningEnd) {
                            $hasMorningAttendance = $attendances->filter(function($att) use ($morningStart, $morningEnd) {
                                return $att->log_time && 
                                       $att->log_time->gte($morningStart) && 
                                       $att->log_time->lte($morningEnd);
                            })->isNotEmpty();
                            
                            if (!$hasMorningAttendance) {
                                $absenceFine = $eventFine;
                            }
                        }
                    } elseif ($scheduleType === 'half_day_afternoon') {
                        // Half day afternoon - if absent, full fine
                        $afternoonStart = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon) : null;
                        $afternoonEnd = $event->end_datetime_afternoon ? \Carbon\Carbon::parse($event->end_datetime_afternoon) : null;
                        
                        if ($afternoonStart && $afternoonEnd) {
                            $hasAfternoonAttendance = $attendances->filter(function($att) use ($afternoonStart, $afternoonEnd) {
                                return $att->log_time && 
                                       $att->log_time->gte($afternoonStart) && 
                                       $att->log_time->lte($afternoonEnd);
                            })->isNotEmpty();
                            
                            if (!$hasAfternoonAttendance) {
                                $absenceFine = $eventFine;
                            }
                        }
                    }
                }
                
                // Calculate late penalty ONLY if student has attendance records
                // If completely absent (no attendance records), skip late penalty calculation
                // Check if log_time exceeds allowed time in events_lates_deduction
                if ($lateRule && !$attendances->isEmpty()) {
                    // Check each attendance record against its corresponding period's allowed time
                    $scheduleType = $event->event_schedule_type ?? 'whole_day';
                    
                    // Morning period check
                    if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_morning') {
                        $morningStart = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning) : null;
                        $morningEnd = $event->end_datetime_morning ? \Carbon\Carbon::parse($event->end_datetime_morning) : null;
                        
                        if ($morningStart && $morningEnd && $lateRule->time_in_morning) {
                            // Parse allowed time in, ensuring proper time format
                            $timeInMorningStr = $lateRule->time_in_morning;
                            if (strlen($timeInMorningStr) == 5) {
                                $timeInMorningStr .= ':00';
                            }
                            $allowedTimeIn = \Carbon\Carbon::parse($morningStart->format('Y-m-d') . ' ' . $timeInMorningStr);
                            
                            // Check time in records within morning period
                            $morningTimeIns = $attendances->where('workstate', 0)->filter(function($att) use ($morningStart, $morningEnd) {
                                return $att->log_time && 
                                       $att->log_time->gte($morningStart) && 
                                       $att->log_time->lte($morningEnd);
                            })->sortBy('log_time');
                            
                            if ($morningTimeIns->isNotEmpty()) {
                                $firstMorningTimeIn = $morningTimeIns->first();
                                // Compare log_time with allowed time in
                                if ($firstMorningTimeIn->log_time && $firstMorningTimeIn->log_time->gt($allowedTimeIn)) {
                                    $latePenalty += ($lateRule->late_penalty ?? 0);
                                }
                            }
                        }
                        
                        if ($morningStart && $morningEnd && $lateRule->time_out_morning) {
                            // Parse allowed time out, ensuring proper time format
                            $timeOutMorningStr = $lateRule->time_out_morning;
                            // If time format is H:i:s, use it; if H:i, append :00
                            if (strlen($timeOutMorningStr) == 5) {
                                $timeOutMorningStr .= ':00';
                            }
                            $allowedTimeOut = \Carbon\Carbon::parse($morningStart->format('Y-m-d') . ' ' . $timeOutMorningStr);
                            
                            // Check time out records within morning period
                            // For whole day events, include time outs slightly after morning end but before afternoon start
                            $afternoonStart = ($scheduleType === 'whole_day' && $event->start_datetime_afternoon) 
                                ? \Carbon\Carbon::parse($event->start_datetime_afternoon) 
                                : null;
                            
                            $morningTimeOuts = $attendances->where('workstate', '!=', 0)->filter(function($att) use ($morningStart, $morningEnd, $afternoonStart, $scheduleType) {
                                if (!$att->log_time) return false;
                                
                                $isOnMorningDate = $att->log_time->format('Y-m-d') === $morningStart->format('Y-m-d');
                                
                                // Check if time out falls within morning period
                                $isInMorningPeriod = $att->log_time->gte($morningStart) && $att->log_time->lte($morningEnd);
                                
                                // For whole day events, also include time outs before afternoon start
                                if ($scheduleType === 'whole_day' && $afternoonStart) {
                                    $isBeforeAfternoon = $att->log_time->lt($afternoonStart);
                                    return $isOnMorningDate && ($isInMorningPeriod || $isBeforeAfternoon);
                                }
                                
                                return $isInMorningPeriod;
                            })->sortByDesc('log_time');
                            
                            if ($morningTimeOuts->isNotEmpty()) {
                                $lastMorningTimeOut = $morningTimeOuts->first();
                                // Compare log_time with allowed time out
                                if ($lastMorningTimeOut->log_time && $lastMorningTimeOut->log_time->gt($allowedTimeOut)) {
                                    $latePenalty += ($lateRule->late_penalty ?? 0);
                                }
                            }
                        }
                    }
                    
                    // Afternoon period check
                    if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_afternoon') {
                        $afternoonStart = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon) : null;
                        $afternoonEnd = $event->end_datetime_afternoon ? \Carbon\Carbon::parse($event->end_datetime_afternoon) : null;
                        
                        if ($afternoonStart && $afternoonEnd && $lateRule->time_in_afternoon) {
                            // Parse allowed time in, ensuring proper time format
                            $timeInAfternoonStr = $lateRule->time_in_afternoon;
                            if (strlen($timeInAfternoonStr) == 5) {
                                $timeInAfternoonStr .= ':00';
                            }
                            $allowedTimeIn = \Carbon\Carbon::parse($afternoonStart->format('Y-m-d') . ' ' . $timeInAfternoonStr);
                            
                            // Check time in records within afternoon period
                            $afternoonTimeIns = $attendances->where('workstate', 0)->filter(function($att) use ($afternoonStart, $afternoonEnd) {
                                return $att->log_time && 
                                       $att->log_time->gte($afternoonStart) && 
                                       $att->log_time->lte($afternoonEnd);
                            })->sortBy('log_time');
                            
                            if ($afternoonTimeIns->isNotEmpty()) {
                                $firstAfternoonTimeIn = $afternoonTimeIns->first();
                                // Compare log_time with allowed time in
                                if ($firstAfternoonTimeIn->log_time && $firstAfternoonTimeIn->log_time->gt($allowedTimeIn)) {
                                    $latePenalty += ($lateRule->late_penalty ?? 0);
                                }
                            }
                        }
                        
                        if ($afternoonStart && $afternoonEnd && $lateRule->time_out_afternoon) {
                            // Parse allowed time out, ensuring proper time format
                            $timeOutAfternoonStr = $lateRule->time_out_afternoon;
                            // If time format is H:i:s, use it; if H:i, append :00
                            if (strlen($timeOutAfternoonStr) == 5) {
                                $timeOutAfternoonStr .= ':00';
                            }
                            $allowedTimeOut = \Carbon\Carbon::parse($afternoonStart->format('Y-m-d') . ' ' . $timeOutAfternoonStr);
                            
                            // Check time out records within afternoon period
                            // For whole day events, include time outs after morning end
                            $morningEnd = ($scheduleType === 'whole_day' && $event->end_datetime_morning) 
                                ? \Carbon\Carbon::parse($event->end_datetime_morning) 
                                : null;
                            
                            $afternoonTimeOuts = $attendances->where('workstate', '!=', 0)->filter(function($att) use ($afternoonStart, $afternoonEnd, $morningEnd, $scheduleType) {
                                if (!$att->log_time) return false;
                                
                                $isOnAfternoonDate = $att->log_time->format('Y-m-d') === $afternoonStart->format('Y-m-d');
                                
                                // Check if time out falls within afternoon period
                                $isInAfternoonPeriod = $att->log_time->gte($afternoonStart) && $att->log_time->lte($afternoonEnd);
                                
                                // For whole day events, also include time outs after morning end
                                if ($scheduleType === 'whole_day' && $morningEnd) {
                                    $isAfterMorning = $att->log_time->gt($morningEnd);
                                    return $isOnAfternoonDate && ($isInAfternoonPeriod || $isAfterMorning);
                                }
                                
                                return $isInAfternoonPeriod;
                            })->sortByDesc('log_time');
                            
                            if ($afternoonTimeOuts->isNotEmpty()) {
                                $lastAfternoonTimeOut = $afternoonTimeOuts->first();
                                // Compare log_time with allowed time out
                                if ($lastAfternoonTimeOut->log_time && $lastAfternoonTimeOut->log_time->gt($allowedTimeOut)) {
                                    $latePenalty += ($lateRule->late_penalty ?? 0);
                                }
                            }
                        }
                    }
                }
                
                // Calculate total penalty
                // If completely absent (no attendance records), total = absence fine only (no late penalty)
                // If partially absent or present, total = absence fine + late penalty
                if ($attendances->isEmpty()) {
                    // Completely absent - no late penalty, only absence fine
                    $latePenalty = 0;
                    $totalPenalty = $absenceFine;
                } else {
                    // Has attendance - total = absence fine + late penalty
                    $totalPenalty = $absenceFine + $latePenalty;
                }
            } elseif ($isAbsent && $event) {
                // Fallback for completely absent (no attendance records and no late deduction info)
                $absenceFine = $event->fines ?? 0;
                $totalPenalty = $absenceFine;
            }
            
            // If completely absent, use absent records instead
            if ($isAbsent && !empty($absentRecords)) {
                $formattedRecords = $absentRecords;
            }
            
            return response()->json([
                'success' => true,
                'event' => $event,
                'attendance_records' => $formattedRecords,
                'is_absent' => $isAbsent,
                'absence_fine' => $absenceFine,
                'late_penalty' => $latePenalty,
                'total_penalty' => $totalPenalty,
                'late_deduction' => $lateDeductionInfo,
                'student' => [
                    'id' => $currentStudent->id,
                    'name' => $currentStudent->student_name,
                    'id_number' => $currentStudent->id_number,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load attendance details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveCartItems(Request $request)
    {
        try {
            // Only students can access this
            if (!auth('students')->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $studentId = auth('students')->user()->id;
            $items = $request->input('items', []); // Array of {student_id, event_id}

            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items selected'
                ], 400);
            }

            $savedCount = 0;
            $errors = [];

            foreach ($items as $item) {
                try {
                    $eventId = $item['event_id'] ?? null;
                    $itemStudentId = $item['student_id'] ?? null;

                    // Validate student_id matches current logged in student
                    if ($itemStudentId != $studentId) {
                        $errors[] = "Invalid student ID for event {$eventId}";
                        continue;
                    }

                    if (!$eventId) {
                        $errors[] = "Missing event ID";
                        continue;
                    }

                    // Get event details
                    $event = events::find($eventId);
                    if (!$event) {
                        $errors[] = "Event not found: {$eventId}";
                        continue;
                    }

                    // Check if payment already exists
                    $existingPayment = attendance_payments::where('students_id', $studentId)
                        ->where('events_id', $eventId)
                        ->where('status', 'active')
                        ->first();

                    if ($existingPayment) {
                        // Update existing payment instead of creating duplicate
                        $payment = $existingPayment;
                    } else {
                        // Create new payment record
                        $payment = attendance_payments::create([
                            'students_id' => $studentId,
                            'events_id' => $eventId,
                            'status' => 'active'
                        ]);
                    }

                    // Get detailed attendance records for penalty calculation
                    // Reuse the same logic from getEventAttendanceDetails method
                    $currentStudent = auth('students')->user();
                    $studentIdForDetails = $currentStudent->id;
                    
                    // Get attendance records for this student and event
                    $attendances = tbl_attendance::with(['student', 'event', 'user'])
                        ->where('student_id', $studentIdForDetails)
                        ->where('event_id', $eventId)
                        ->where('status', 'active')
                        ->orderBy('log_time', 'asc')
                        ->get();
                    
                    // Get participant status
                    $participant = events_list_of_participants::whereHas('events_assign_participants.events', function($q) use ($eventId) {
                            $q->where('id', $eventId)->where('status', 'active');
                        })
                        ->where('students_id', $studentIdForDetails)
                        ->where('status', 'active')
                        ->first();
                    
                    $isParticipant = $participant !== null;
                    
                    // Format attendance records
                    $attendanceRecords = [];
                    foreach ($attendances as $attendance) {
                        $attendanceRecords[] = (object)[
                            'id' => $attendance->id,
                            'log_time' => $attendance->log_time ? $attendance->log_time->format('Y-m-d H:i:s') : null,
                            'workstate_code' => $attendance->workstate,
                        ];
                    }
                    
                    // Check for absences based on event_schedule_type
                    $scheduleType = $event->event_schedule_type ?? 'whole_day';
                    $hasMorningAttendance = false;
                    $hasAfternoonAttendance = false;
                    $absenceFine = 0;
                    $latePenalty = 0;
                    $totalPenalty = 0;
                    
                    // Calculate absence fine
                    if ($isParticipant && $event) {
                        $eventFine = $event->fines ?? 0;
                        
                        if ($attendances->isEmpty()) {
                            $absenceFine = $eventFine;
                        } else {
                            // Check partial absences for whole day events
                            if ($scheduleType === 'whole_day') {
                                $morningStart = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning) : null;
                                $morningEnd = $event->end_datetime_morning ? \Carbon\Carbon::parse($event->end_datetime_morning) : null;
                                $afternoonStart = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon) : null;
                                $afternoonEnd = $event->end_datetime_afternoon ? \Carbon\Carbon::parse($event->end_datetime_afternoon) : null;
                                
                                if ($morningStart && $morningEnd) {
                                    $hasMorningAttendance = $attendances->filter(function($att) use ($morningStart, $morningEnd) {
                                        return $att->log_time && 
                                               $att->log_time->gte($morningStart) && 
                                               $att->log_time->lte($morningEnd);
                                    })->isNotEmpty();
                                }
                                
                                if ($afternoonStart && $afternoonEnd) {
                                    $hasAfternoonAttendance = $attendances->filter(function($att) use ($afternoonStart, $afternoonEnd) {
                                        return $att->log_time && 
                                               $att->log_time->gte($afternoonStart) && 
                                               $att->log_time->lte($afternoonEnd);
                                    })->isNotEmpty();
                                }
                                
                                if (!$hasMorningAttendance && !$hasAfternoonAttendance) {
                                    $absenceFine = $eventFine;
                                } elseif (!$hasMorningAttendance || !$hasAfternoonAttendance) {
                                    $absenceFine = $eventFine / 2;
                                }
                            } elseif ($scheduleType === 'half_day_morning') {
                                $morningStart = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning) : null;
                                $morningEnd = $event->end_datetime_morning ? \Carbon\Carbon::parse($event->end_datetime_morning) : null;
                                
                                if ($morningStart && $morningEnd) {
                                    $hasMorningAttendance = $attendances->filter(function($att) use ($morningStart, $morningEnd) {
                                        return $att->log_time && 
                                               $att->log_time->gte($morningStart) && 
                                               $att->log_time->lte($morningEnd);
                                    })->isNotEmpty();
                                    
                                    if (!$hasMorningAttendance) {
                                        $absenceFine = $eventFine;
                                    }
                                }
                            } elseif ($scheduleType === 'half_day_afternoon') {
                                $afternoonStart = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon) : null;
                                $afternoonEnd = $event->end_datetime_afternoon ? \Carbon\Carbon::parse($event->end_datetime_afternoon) : null;
                                
                                if ($afternoonStart && $afternoonEnd) {
                                    $hasAfternoonAttendance = $attendances->filter(function($att) use ($afternoonStart, $afternoonEnd) {
                                        return $att->log_time && 
                                               $att->log_time->gte($afternoonStart) && 
                                               $att->log_time->lte($afternoonEnd);
                                    })->isNotEmpty();
                                    
                                    if (!$hasAfternoonAttendance) {
                                        $absenceFine = $eventFine;
                                    }
                                }
                            }
                        }
                    }
                    
                    // Calculate late penalty (simplified - using the same logic from getEventAttendanceDetails)
                    $lateRule = events_lates_deduction::where('events_id', $eventId)
                        ->where('status', 'active')
                        ->orderBy('id', 'desc')
                        ->first();
                    
                    if ($lateRule && $attendances->isNotEmpty()) {
                        // Similar logic to getEventAttendanceDetails for calculating late penalties
                        // For brevity, we'll calculate based on actual violations
                        // This matches the logic in getEventAttendanceDetails method
                        if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_morning') {
                            $morningStart = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning) : null;
                            $morningEnd = $event->end_datetime_morning ? \Carbon\Carbon::parse($event->end_datetime_morning) : null;
                            
                            if ($morningStart && $morningEnd && $lateRule->time_in_morning) {
                                $timeInMorningStr = $lateRule->time_in_morning;
                                if (strlen($timeInMorningStr) == 5) {
                                    $timeInMorningStr .= ':00';
                                }
                                $allowedTimeIn = \Carbon\Carbon::parse($morningStart->format('Y-m-d') . ' ' . $timeInMorningStr);
                                
                                $morningTimeIns = $attendances->where('workstate', 0)->filter(function($att) use ($morningStart, $morningEnd) {
                                    return $att->log_time && 
                                           $att->log_time->gte($morningStart) && 
                                           $att->log_time->lte($morningEnd);
                                })->sortBy('log_time');
                                
                                if ($morningTimeIns->isNotEmpty()) {
                                    $firstMorningTimeIn = $morningTimeIns->first();
                                    if ($firstMorningTimeIn->log_time && $firstMorningTimeIn->log_time->gt($allowedTimeIn)) {
                                        $latePenalty += ($lateRule->late_penalty ?? 0);
                                    }
                                }
                            }
                            
                            if ($morningStart && $morningEnd && $lateRule->time_out_morning) {
                                $timeOutMorningStr = $lateRule->time_out_morning;
                                if (strlen($timeOutMorningStr) == 5) {
                                    $timeOutMorningStr .= ':00';
                                }
                                $allowedTimeOut = \Carbon\Carbon::parse($morningStart->format('Y-m-d') . ' ' . $timeOutMorningStr);
                                
                                $morningTimeOuts = $attendances->where('workstate', '!=', 0)->filter(function($att) use ($morningStart, $morningEnd) {
                                    return $att->log_time && 
                                           $att->log_time->gte($morningStart) && 
                                           $att->log_time->lte($morningEnd);
                                })->sortByDesc('log_time');
                                
                                if ($morningTimeOuts->isNotEmpty()) {
                                    $lastMorningTimeOut = $morningTimeOuts->first();
                                    if ($lastMorningTimeOut->log_time && $lastMorningTimeOut->log_time->gt($allowedTimeOut)) {
                                        $latePenalty += ($lateRule->late_penalty ?? 0);
                                    }
                                }
                            }
                        }
                        
                        if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_afternoon') {
                            $afternoonStart = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon) : null;
                            $afternoonEnd = $event->end_datetime_afternoon ? \Carbon\Carbon::parse($event->end_datetime_afternoon) : null;
                            
                            if ($afternoonStart && $afternoonEnd && $lateRule->time_in_afternoon) {
                                $timeInAfternoonStr = $lateRule->time_in_afternoon;
                                if (strlen($timeInAfternoonStr) == 5) {
                                    $timeInAfternoonStr .= ':00';
                                }
                                $allowedTimeIn = \Carbon\Carbon::parse($afternoonStart->format('Y-m-d') . ' ' . $timeInAfternoonStr);
                                
                                $afternoonTimeIns = $attendances->where('workstate', 0)->filter(function($att) use ($afternoonStart, $afternoonEnd) {
                                    return $att->log_time && 
                                           $att->log_time->gte($afternoonStart) && 
                                           $att->log_time->lte($afternoonEnd);
                                })->sortBy('log_time');
                                
                                if ($afternoonTimeIns->isNotEmpty()) {
                                    $firstAfternoonTimeIn = $afternoonTimeIns->first();
                                    if ($firstAfternoonTimeIn->log_time && $firstAfternoonTimeIn->log_time->gt($allowedTimeIn)) {
                                        $latePenalty += ($lateRule->late_penalty ?? 0);
                                    }
                                }
                            }
                            
                            if ($afternoonStart && $afternoonEnd && $lateRule->time_out_afternoon) {
                                $timeOutAfternoonStr = $lateRule->time_out_afternoon;
                                if (strlen($timeOutAfternoonStr) == 5) {
                                    $timeOutAfternoonStr .= ':00';
                                }
                                $allowedTimeOut = \Carbon\Carbon::parse($afternoonStart->format('Y-m-d') . ' ' . $timeOutAfternoonStr);
                                
                                $afternoonTimeOuts = $attendances->where('workstate', '!=', 0)->filter(function($att) use ($afternoonStart, $afternoonEnd) {
                                    return $att->log_time && 
                                           $att->log_time->gte($afternoonStart) && 
                                           $att->log_time->lte($afternoonEnd);
                                })->sortByDesc('log_time');
                                
                                if ($afternoonTimeOuts->isNotEmpty()) {
                                    $lastAfternoonTimeOut = $afternoonTimeOuts->first();
                                    if ($lastAfternoonTimeOut->log_time && $lastAfternoonTimeOut->log_time->gt($allowedTimeOut)) {
                                        $latePenalty += ($lateRule->late_penalty ?? 0);
                                    }
                                }
                            }
                        }
                    }
                    
                    $totalPenalty = $absenceFine + $latePenalty;

                    // Update payment record with total amount and payment status
                    $payment->amount_paid = $totalPenalty;
                    $payment->payment_status = 'pending';
                    $payment->save();

                    // Delete existing time schedules for this payment
                    attendance_payments_time_schedule::where('attendance_payments_id', $payment->id)
                        ->where('status', 'active')
                        ->delete();

                    // Determine schedule type for payment
                    $typeOfSchedulePay = 'whole_day';
                    if ($scheduleType === 'half_day_morning') {
                        $typeOfSchedulePay = 'morning';
                    } elseif ($scheduleType === 'half_day_afternoon') {
                        $typeOfSchedulePay = 'afternoon';
                    } else {
                        $typeOfSchedulePay = 'whole_day';
                    }

                    // Save absence fine if any
                    if ($absenceFine > 0) {
                        // Build missing period(s) and create both start (time in) and end (time out) logs
                        if ($scheduleType === 'whole_day') {
                            if (!$hasMorningAttendance && !$hasAfternoonAttendance) {
                                // Whole day absent: create morning start (IN) and afternoon end (OUT)
                                if ($event->start_datetime_morning) {
                                    attendance_payments_time_schedule::create([
                                        'attendance_payments_id' => $payment->id,
                                        'type_of_schedule_pay' => 'morning',
                                        'log_time' => $event->start_datetime_morning,
                                        'workstate' => 0,
                                        'status' => 'active'
                                    ]);
                                }
                                if ($event->end_datetime_afternoon) {
                                    attendance_payments_time_schedule::create([
                                        'attendance_payments_id' => $payment->id,
                                        'type_of_schedule_pay' => 'afternoon',
                                        'log_time' => $event->end_datetime_afternoon,
                                        'workstate' => 1,
                                        'status' => 'active'
                                    ]);
                                }
                            } elseif (!$hasMorningAttendance && $hasAfternoonAttendance) {
                                // Absent in morning period only: save morning start (IN) and morning end (OUT)
                                if ($event->start_datetime_morning) {
                                    attendance_payments_time_schedule::create([
                                        'attendance_payments_id' => $payment->id,
                                        'type_of_schedule_pay' => 'morning',
                                        'log_time' => $event->start_datetime_morning,
                                        'workstate' => 0,
                                        'status' => 'active'
                                    ]);
                                }
                                if ($event->end_datetime_morning) {
                                    attendance_payments_time_schedule::create([
                                        'attendance_payments_id' => $payment->id,
                                        'type_of_schedule_pay' => 'morning',
                                        'log_time' => $event->end_datetime_morning,
                                        'workstate' => 1,
                                        'status' => 'active'
                                    ]);
                                }
                            } elseif ($hasMorningAttendance && !$hasAfternoonAttendance) {
                                // Absent in afternoon period only: save afternoon start (IN) and afternoon end (OUT)
                                if ($event->start_datetime_afternoon) {
                                    attendance_payments_time_schedule::create([
                                        'attendance_payments_id' => $payment->id,
                                        'type_of_schedule_pay' => 'afternoon',
                                        'log_time' => $event->start_datetime_afternoon,
                                        'workstate' => 0,
                                        'status' => 'active'
                                    ]);
                                }
                                if ($event->end_datetime_afternoon) {
                                    attendance_payments_time_schedule::create([
                                        'attendance_payments_id' => $payment->id,
                                        'type_of_schedule_pay' => 'afternoon',
                                        'log_time' => $event->end_datetime_afternoon,
                                        'workstate' => 1,
                                        'status' => 'active'
                                    ]);
                                }
                            }
                        } elseif ($scheduleType === 'half_day_morning') {
                            // Half day morning absent: save morning start (IN) and morning end (OUT)
                            if (!$hasMorningAttendance) {
                                if ($event->start_datetime_morning) {
                                    attendance_payments_time_schedule::create([
                                        'attendance_payments_id' => $payment->id,
                                        'type_of_schedule_pay' => 'morning',
                                        'log_time' => $event->start_datetime_morning,
                                        'workstate' => 0,
                                        'status' => 'active'
                                    ]);
                                }
                                if ($event->end_datetime_morning) {
                                    attendance_payments_time_schedule::create([
                                        'attendance_payments_id' => $payment->id,
                                        'type_of_schedule_pay' => 'morning',
                                        'log_time' => $event->end_datetime_morning,
                                        'workstate' => 1,
                                        'status' => 'active'
                                    ]);
                                }
                            }
                        } elseif ($scheduleType === 'half_day_afternoon') {
                            // Half day afternoon absent: save afternoon start (IN) and afternoon end (OUT)
                            if (!$hasAfternoonAttendance) {
                                if ($event->start_datetime_afternoon) {
                                    attendance_payments_time_schedule::create([
                                        'attendance_payments_id' => $payment->id,
                                        'type_of_schedule_pay' => 'afternoon',
                                        'log_time' => $event->start_datetime_afternoon,
                                        'workstate' => 0,
                                        'status' => 'active'
                                    ]);
                                }
                                if ($event->end_datetime_afternoon) {
                                    attendance_payments_time_schedule::create([
                                        'attendance_payments_id' => $payment->id,
                                        'type_of_schedule_pay' => 'afternoon',
                                        'log_time' => $event->end_datetime_afternoon,
                                        'workstate' => 1,
                                        'status' => 'active'
                                    ]);
                                }
                            }
                        }
                    }

                    // Save late penalties for time in violations
                    // Use the $lateRule already fetched during penalty calculation
                    if ($latePenalty > 0 && isset($lateRule) && $lateRule) {
                        // Check morning time in violations
                        if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_morning') {
                            $morningStart = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning) : null;
                            $morningEnd = $event->end_datetime_morning ? \Carbon\Carbon::parse($event->end_datetime_morning) : null;

                            if ($morningStart && $morningEnd && $lateRule->time_in_morning) {
                                $timeInMorningStr = $lateRule->time_in_morning;
                                if (strlen($timeInMorningStr) == 5) {
                                    $timeInMorningStr .= ':00';
                                }
                                $allowedTimeIn = \Carbon\Carbon::parse($morningStart->format('Y-m-d') . ' ' . $timeInMorningStr);

                                foreach ($attendanceRecords as $record) {
                                    if (isset($record->workstate_code) && $record->workstate_code == 0 && isset($record->log_time)) {
                                        $logTime = \Carbon\Carbon::parse($record->log_time);
                                        if ($logTime->gte($morningStart) && $logTime->lte($morningEnd) && $logTime->gt($allowedTimeIn)) {
                                            attendance_payments_time_schedule::create([
                                                'attendance_payments_id' => $payment->id,
                                                'type_of_schedule_pay' => 'morning',
                                                'log_time' => 0, // 0 for time in
                                                'workstate' => 0,
                                                'status' => 'active'
                                            ]);
                                        }
                                    }
                                }
                            }
                        }

                        // Check morning time out violations
                        if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_morning') {
                            $morningStart = $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning) : null;
                            $morningEnd = $event->end_datetime_morning ? \Carbon\Carbon::parse($event->end_datetime_morning) : null;

                            if ($morningStart && $morningEnd && $lateRule->time_out_morning) {
                                $timeOutMorningStr = $lateRule->time_out_morning;
                                if (strlen($timeOutMorningStr) == 5) {
                                    $timeOutMorningStr .= ':00';
                                }
                                $allowedTimeOut = \Carbon\Carbon::parse($morningStart->format('Y-m-d') . ' ' . $timeOutMorningStr);

                                foreach ($attendanceRecords as $record) {
                                    if (isset($record->workstate_code) && $record->workstate_code != 0 && isset($record->log_time)) {
                                        $logTime = \Carbon\Carbon::parse($record->log_time);
                                        if ($logTime->gte($morningStart) && $logTime->lte($morningEnd) && $logTime->gt($allowedTimeOut)) {
                                            attendance_payments_time_schedule::create([
                                                'attendance_payments_id' => $payment->id,
                                                'type_of_schedule_pay' => 'morning',
                                                'log_time' => 1, // 1 for time out
                                                'workstate' => 1,
                                                'status' => 'active'
                                            ]);
                                        }
                                    }
                                }
                            }
                        }

                        // Check afternoon time in violations
                        if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_afternoon') {
                            $afternoonStart = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon) : null;
                            $afternoonEnd = $event->end_datetime_afternoon ? \Carbon\Carbon::parse($event->end_datetime_afternoon) : null;

                            if ($afternoonStart && $afternoonEnd && $lateRule->time_in_afternoon) {
                                $timeInAfternoonStr = $lateRule->time_in_afternoon;
                                if (strlen($timeInAfternoonStr) == 5) {
                                    $timeInAfternoonStr .= ':00';
                                }
                                $allowedTimeIn = \Carbon\Carbon::parse($afternoonStart->format('Y-m-d') . ' ' . $timeInAfternoonStr);

                                foreach ($attendanceRecords as $record) {
                                    if (isset($record->workstate_code) && $record->workstate_code == 0 && isset($record->log_time)) {
                                        $logTime = \Carbon\Carbon::parse($record->log_time);
                                        if ($logTime->gte($afternoonStart) && $logTime->lte($afternoonEnd) && $logTime->gt($allowedTimeIn)) {
                                            attendance_payments_time_schedule::create([
                                                'attendance_payments_id' => $payment->id,
                                                'type_of_schedule_pay' => 'afternoon',
                                                'log_time' => 0, // 0 for time in
                                                'workstate' => 0,
                                                'status' => 'active'
                                            ]);
                                        }
                                    }
                                }
                            }
                        }

                        // Check afternoon time out violations
                        if ($scheduleType === 'whole_day' || $scheduleType === 'half_day_afternoon') {
                            $afternoonStart = $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon) : null;
                            $afternoonEnd = $event->end_datetime_afternoon ? \Carbon\Carbon::parse($event->end_datetime_afternoon) : null;

                            if ($afternoonStart && $afternoonEnd && $lateRule->time_out_afternoon) {
                                $timeOutAfternoonStr = $lateRule->time_out_afternoon;
                                if (strlen($timeOutAfternoonStr) == 5) {
                                    $timeOutAfternoonStr .= ':00';
                                }
                                $allowedTimeOut = \Carbon\Carbon::parse($afternoonStart->format('Y-m-d') . ' ' . $timeOutAfternoonStr);

                                foreach ($attendanceRecords as $record) {
                                    if (isset($record->workstate_code) && $record->workstate_code != 0 && isset($record->log_time)) {
                                        $logTime = \Carbon\Carbon::parse($record->log_time);
                                        if ($logTime->gte($afternoonStart) && $logTime->lte($afternoonEnd) && $logTime->gt($allowedTimeOut)) {
                                            attendance_payments_time_schedule::create([
                                                'attendance_payments_id' => $payment->id,
                                                'type_of_schedule_pay' => 'afternoon',
                                                'log_time' => 1, // 1 for time out
                                                'workstate' => 1,
                                                'status' => 'active'
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $savedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error processing event {$item['event_id']}: " . $e->getMessage();
                }
            }

            if ($savedCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully added {$savedCount} item(s) to cart",
                    'errors' => $errors
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add items to cart',
                    'errors' => $errors
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save cart items: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getReceiptDetails($paymentId)
    {
        try {
            // Only students can access this
            if (!auth('students')->check()) {
                return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            }
            
            $currentStudent = auth('students')->user();
            $studentId = $currentStudent->id;
            
            // Get payment and verify it belongs to the current student
            $payment = attendance_payments::with([
                'students.college',
                'students.program',
                'students.organization',
                'events'
            ])
            ->where('id', $paymentId)
            ->where('students_id', $studentId)
            ->where('status', 'active')
            ->where('payment_status', 'approved')
            ->first();
            
            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment receipt not found or not approved'
                ], 404);
            }
            
            // Get receipt
            $receipt = generated_receipt::where('attendance_payments_id', $paymentId)
                ->where('status', 'active')
                ->first();
            
            if (!$receipt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receipt not found'
                ], 404);
            }
            
            // Get time schedules
            $timeSchedules = attendance_payments_time_schedule::where('attendance_payments_id', $paymentId)
                ->where('status', 'active')
                ->orderBy('log_time', 'asc')
                ->get();
            
            $student = $payment->students;
            $event = $payment->events;
            
            // Calculate original amount before waiver
            $originalAmount = ($payment->amount_paid ?? 0) + ($payment->waiver_amount ?? 0);
            
            return response()->json([
                'success' => true,
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
                'message' => 'Failed to load receipt: ' . $e->getMessage()
            ], 500);
        }
    }
}
