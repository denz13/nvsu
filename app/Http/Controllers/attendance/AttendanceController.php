<?php

namespace App\Http\Controllers\attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\events;
use App\Models\tbl_attendance;
use App\Models\events_assign_participants;
use App\Models\events_list_of_participants;
use App\Models\events_lates_deduction;

class AttendanceController extends Controller
{
    public function attendance()
    {
        // Get all events ordered by most recent
        $events = events::orderBy('created_at', 'desc')->get();
        
        return view('attendance.attendance', compact('events'));
    }
    
    public function getAttendance(Request $request)
    {
        try {
            $eventId = $request->input('event_id');
            $perPage = $request->input('per_page', 10); // Default 10 items per page
            
            $query = tbl_attendance::with(['student', 'event', 'user'])
                ->where('status', 'active')
                ->orderBy('log_time', 'desc');

            // Filter by event if specified
            if ($eventId && $eventId !== 'all') {
                $query->where('event_id', $eventId);

                // Only include students who are assigned as participants for this event
                $assignmentIds = events_assign_participants::where('events_id', $eventId)
                    ->where('status', 'active')
                    ->pluck('id');
                if ($assignmentIds->count() > 0) {
                    $participantStudentIds = events_list_of_participants::whereIn('events_assign_participants_id', $assignmentIds)
                        ->where('status', 'active')
                        ->pluck('students_id');
                    // If there are participants defined, limit results to them; otherwise, return empty set
                    if ($participantStudentIds->count() > 0) {
                        $query->whereIn('student_id', $participantStudentIds);
                    } else {
                        return response()->json([
                            'success' => true,
                            'attendances' => [],
                            'pagination' => [
                                'current_page' => (int)$request->input('page', 1),
                                'per_page' => (int)$request->input('per_page', 10),
                                'total' => 0,
                                'last_page' => 0,
                                'from' => 0,
                                'to' => 0,
                                'base_url' => '/attendance/list?page=',
                                'event_param' => "&event_id={$eventId}",
                                'has_more' => false,
                                'on_first_page' => true,
                            ]
                        ]);
                    }
                } else {
                    // No assignments for this event; return empty set
                    return response()->json([
                        'success' => true,
                        'attendances' => [],
                        'pagination' => [
                            'current_page' => (int)$request->input('page', 1),
                            'per_page' => (int)$request->input('per_page', 10),
                            'total' => 0,
                            'last_page' => 0,
                            'from' => 0,
                            'to' => 0,
                            'base_url' => '/attendance/list?page=',
                            'event_param' => "&event_id={$eventId}",
                            'has_more' => false,
                            'on_first_page' => true,
                        ]
                    ]);
                }
            }
            
            // Get all records first (needed for grouping)
            $attendances = $query->get();
            
            // Group by student_id and event_id - combine time in and time out
            $groupedAttendances = [];
            
            foreach ($attendances as $attendance) {
                $key = $attendance->student_id . '_' . $attendance->event_id;
                
                if (!isset($groupedAttendances[$key])) {
                    // Initialize grouped attendance
                    $groupedAttendances[$key] = [
                        'id' => $attendance->id, // Keep most recent ID
                        'student_id' => $attendance->student_id,
                        'event_id' => $attendance->event_id,
                        'student_name' => $attendance->student ? $attendance->student->student_name : 'N/A',
                        'student_id_number' => $attendance->student ? $attendance->student->id_number : 'N/A',
                        'student_photo' => $attendance->student ? $attendance->student->photo : null,
                        'event_name' => $attendance->event ? $attendance->event->event_name : 'N/A',
                        'time_in' => null,
                        'time_in_formatted' => null,
                        'time_out' => null,
                        'time_out_formatted' => null,
                        'time_in_id' => null,
                        'time_out_id' => null,
                        'latest_time' => null,
                        'latest_time_formatted' => null,
                        'latest_workstate' => null,
                        'latest_workstate_text' => null,
                        'scan_by' => $attendance->scan_by ?? 'N/A',
                        'user_name' => $attendance->user ? $attendance->user->name : 'N/A',
                    ];
                }
                
                // Store the latest time in and time out records
                // Add time in or time out based on workstate
                if ($attendance->workstate == "0" || $attendance->workstate == 0) {
                    // Time In - keep the latest one
                    $existingTimeIn = $groupedAttendances[$key]['time_in'];
                    if (!$existingTimeIn) {
                        // First time in
                        $groupedAttendances[$key]['time_in'] = $attendance->log_time ? $attendance->log_time->format('Y-m-d H:i:s') : null;
                        $groupedAttendances[$key]['time_in_formatted'] = $attendance->log_time ? $attendance->log_time->format('M d, Y h:i A') : null;
                        $groupedAttendances[$key]['time_in_id'] = $attendance->id;
                    } else {
                        // Compare timestamps - keep the latest
                        $existingTimeInCarbon = \Carbon\Carbon::parse($existingTimeIn);
                        if ($attendance->log_time && $attendance->log_time->gt($existingTimeInCarbon)) {
                            $groupedAttendances[$key]['time_in'] = $attendance->log_time->format('Y-m-d H:i:s');
                            $groupedAttendances[$key]['time_in_formatted'] = $attendance->log_time->format('M d, Y h:i A');
                            $groupedAttendances[$key]['time_in_id'] = $attendance->id;
                        }
                    }
                } else {
                    // Time Out - keep the latest one
                    $existingTimeOut = $groupedAttendances[$key]['time_out'];
                    if (!$existingTimeOut) {
                        // First time out
                        $groupedAttendances[$key]['time_out'] = $attendance->log_time ? $attendance->log_time->format('Y-m-d H:i:s') : null;
                        $groupedAttendances[$key]['time_out_formatted'] = $attendance->log_time ? $attendance->log_time->format('M d, Y h:i A') : null;
                        $groupedAttendances[$key]['time_out_id'] = $attendance->id;
                    } else {
                        // Compare timestamps - keep the latest
                        $existingTimeOutCarbon = \Carbon\Carbon::parse($existingTimeOut);
                        if ($attendance->log_time && $attendance->log_time->gt($existingTimeOutCarbon)) {
                            $groupedAttendances[$key]['time_out'] = $attendance->log_time->format('Y-m-d H:i:s');
                            $groupedAttendances[$key]['time_out_formatted'] = $attendance->log_time->format('M d, Y h:i A');
                            $groupedAttendances[$key]['time_out_id'] = $attendance->id;
                        }
                    }
                }
                
                // Update latest time for status indicator (most recent attendance record)
                $existingLatestTime = $groupedAttendances[$key]['latest_time'];
                if (!$existingLatestTime) {
                    // First record
                    $groupedAttendances[$key]['latest_time'] = $attendance->log_time ? $attendance->log_time->format('Y-m-d H:i:s') : null;
                    $groupedAttendances[$key]['latest_time_formatted'] = $attendance->log_time ? $attendance->log_time->format('M d, Y h:i A') : null;
                    $groupedAttendances[$key]['latest_workstate'] = $attendance->workstate;
                    $groupedAttendances[$key]['latest_workstate_text'] = $attendance->workstate == "0" || $attendance->workstate == 0 ? 'Time In' : 'Time Out';
                    $groupedAttendances[$key]['id'] = $attendance->id;
                } else {
                    // Compare timestamps - keep the latest
                    $existingLatestTimeCarbon = \Carbon\Carbon::parse($existingLatestTime);
                    if ($attendance->log_time && $attendance->log_time->gt($existingLatestTimeCarbon)) {
                        $groupedAttendances[$key]['latest_time'] = $attendance->log_time->format('Y-m-d H:i:s');
                        $groupedAttendances[$key]['latest_time_formatted'] = $attendance->log_time->format('M d, Y h:i A');
                        $groupedAttendances[$key]['latest_workstate'] = $attendance->workstate;
                        $groupedAttendances[$key]['latest_workstate_text'] = $attendance->workstate == "0" || $attendance->workstate == 0 ? 'Time In' : 'Time Out';
                        $groupedAttendances[$key]['id'] = $attendance->id;
                    }
                }
            }
            
            // Convert grouped array to indexed array and sort by latest time
            $formattedAttendances = collect($groupedAttendances)->values()->sortByDesc('latest_time')->values();
            
            // Find participants who have no attendance (absences) - only if event_id is specified
            if ($eventId && $eventId !== 'all') {
                $event = events::find($eventId);
                if ($event) {
                    // Get all participants for this event
                    $assignmentIds = events_assign_participants::where('events_id', $eventId)
                        ->where('status', 'active')
                        ->pluck('id');
                    
                    if ($assignmentIds->count() > 0) {
                        $participantStudents = events_list_of_participants::whereIn('events_assign_participants_id', $assignmentIds)
                            ->where('status', 'active')
                            ->with(['students'])
                            ->get();
                        
                        foreach ($participantStudents as $participant) {
                            if (!$participant->students) continue;
                            $studentId = $participant->students_id;
                            $eventKey = $studentId . '_' . $eventId;
                            
                            // Check if already in groupedAttendances
                            $hasAttendance = $formattedAttendances->first(function($item) use ($eventKey) {
                                return ($item['student_id'] . '_' . $item['event_id']) === $eventKey;
                            });
                            
                            if (!$hasAttendance) {
                                // Student is participant but has no attendance - mark as absent
                                $lateRule = events_lates_deduction::where('events_id', $eventId)
                                    ->where('status', 'active')
                                    ->orderBy('id', 'desc')
                                    ->first();
                                
                                $formattedAttendances->push([
                                    'id' => null,
                                    'student_id' => $studentId,
                                    'event_id' => $eventId,
                                    'student_name' => $participant->students->student_name ?? 'N/A',
                                    'student_id_number' => $participant->students->id_number ?? 'N/A',
                                    'student_photo' => $participant->students->photo ?? null,
                                    'event_name' => $event->event_name ?? 'N/A',
                                    'time_in' => null,
                                    'time_in_formatted' => null,
                                    'time_out' => null,
                                    'time_out_formatted' => null,
                                    'time_in_id' => null,
                                    'time_out_id' => null,
                                    'latest_time' => $event->start_datetime,
                                    'latest_time_formatted' => $event->start_datetime ? \Carbon\Carbon::parse($event->start_datetime)->format('M d, Y h:i A') : null,
                                    'latest_workstate' => null,
                                    'latest_workstate_text' => 'Absent',
                                    'scan_by' => 'N/A',
                                    'user_name' => 'N/A',
                                    'status' => 'Absent',
                                    'absence_fine' => $event->fines ?? 0,
                                    'late_penalty' => 0,
                                    'total_penalty' => $event->fines ?? 0,
                                ]);
                            } else {
                                // Has attendance - calculate late penalties
                                $attendanceItem = $formattedAttendances->first(function($item) use ($eventKey) {
                                    return ($item['student_id'] . '_' . $item['event_id']) === $eventKey;
                                });
                                
                                if ($attendanceItem) {
                                    $lateRule = events_lates_deduction::where('events_id', $eventId)
                                        ->where('status', 'active')
                                        ->orderBy('id', 'desc')
                                        ->first();
                                    
                                    $latePenalty = 0;
                                    $eventDate = \Carbon\Carbon::parse($event->start_datetime);
                                    $allowedTimeIn = $lateRule && $lateRule->time_in ? \Carbon\Carbon::parse($eventDate->format('Y-m-d') . ' ' . $lateRule->time_in) : null;
                                    $allowedTimeOut = $lateRule && $lateRule->time_out ? \Carbon\Carbon::parse($eventDate->format('Y-m-d') . ' ' . $lateRule->time_out) : null;
                                    
                                    if ($attendanceItem['time_in']) {
                                        $actualTimeIn = \Carbon\Carbon::parse($attendanceItem['time_in']);
                                        if ($allowedTimeIn && $actualTimeIn->gt($allowedTimeIn)) {
                                            $latePenalty += ($lateRule->late_penalty ?? 0);
                                        }
                                    }
                                    
                                    if ($attendanceItem['time_out'] && $allowedTimeOut) {
                                        $actualTimeOut = \Carbon\Carbon::parse($attendanceItem['time_out']);
                                        if ($actualTimeOut->gt($allowedTimeOut)) {
                                            $latePenalty += ($lateRule->late_penalty ?? 0);
                                        }
                                    }
                                    
                                    // Update the item using collection map method
                                    $formattedAttendances = $formattedAttendances->map(function($item) use ($eventKey, $latePenalty) {
                                        if (($item['student_id'] . '_' . $item['event_id']) === $eventKey) {
                                            $item['late_penalty'] = $latePenalty;
                                            $item['total_penalty'] = $latePenalty;
                                            $item['absence_fine'] = 0;
                                            if (!isset($item['status'])) {
                                                $item['status'] = 'Present';
                                            }
                                        }
                                        return $item;
                                    });
                                }
                            }
                        }
                    }
                }
            }
            
            // Re-sort after adding absences
            $formattedAttendances = $formattedAttendances->sortByDesc('latest_time')->values();
            
            // Manual pagination for grouped data
            $currentPage = (int)$request->input('page', 1);
            $perPage = (int)$request->input('per_page', 10);
            $total = $formattedAttendances->count();
            $lastPage = (int)ceil($total / $perPage);
            $offset = ($currentPage - 1) * $perPage;
            $paginatedAttendances = $formattedAttendances->slice($offset, $perPage)->values()->toArray();
            
            // Build pagination URLs
            $eventParam = $eventId && $eventId !== 'all' ? "&event_id={$eventId}" : '';
            $baseUrl = '/attendance/list?page=';
            
            return response()->json([
                'success' => true,
                'attendances' => $paginatedAttendances,
                'pagination' => [
                    'current_page' => $currentPage,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => $lastPage,
                    'from' => $total > 0 ? $offset + 1 : 0,
                    'to' => min($offset + $perPage, $total),
                    'base_url' => $baseUrl,
                    'event_param' => $eventParam,
                    'has_more' => $currentPage < $lastPage,
                    'on_first_page' => $currentPage == 1
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getStudentAttendance(Request $request)
    {
        try {
            $studentId = $request->input('student_id');
            $eventId = $request->input('event_id');
            
            if (!$studentId || !$eventId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required parameters: student_id and event_id'
                ], 400);
            }
            
            // Get all attendance records for this student and event
            $attendances = tbl_attendance::with(['student', 'event', 'user'])
                ->where('student_id', $studentId)
                ->where('event_id', $eventId)
                ->where('status', 'active')
                ->orderBy('log_time', 'asc')
                ->get();
            
            // Format the data
            $formattedAttendances = $attendances->map(function($attendance) {
                return [
                    'id' => $attendance->id,
                    'log_time' => $attendance->log_time ? $attendance->log_time->format('Y-m-d H:i:s') : null,
                    'log_time_formatted' => $attendance->log_time ? $attendance->log_time->format('M d, Y h:i A') : null,
                    'workstate' => $attendance->workstate,
                    'workstate_text' => $attendance->workstate == "0" || $attendance->workstate == 0 ? 'Time In' : 'Time Out',
                    'scan_by' => $attendance->scan_by ?? 'N/A',
                    'user_name' => $attendance->user ? $attendance->user->name : 'N/A',
                ];
            });
            
            // Get student and event info from first record
            $studentInfo = null;
            $eventInfo = null;
            if ($attendances->count() > 0) {
                $firstAttendance = $attendances->first();
                $studentInfo = [
                    'id' => $firstAttendance->student_id,
                    'name' => $firstAttendance->student ? $firstAttendance->student->student_name : 'N/A',
                    'id_number' => $firstAttendance->student ? $firstAttendance->student->id_number : 'N/A',
                    'photo' => $firstAttendance->student ? $firstAttendance->student->photo : null,
                ];
                $eventInfo = [
                    'id' => $firstAttendance->event_id,
                    'name' => $firstAttendance->event ? $firstAttendance->event->event_name : 'N/A',
                ];
            }
            
            return response()->json([
                'success' => true,
                'student' => $studentInfo,
                'event' => $eventInfo,
                'attendances' => $formattedAttendances,
                'count' => $formattedAttendances->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student attendance: ' . $e->getMessage()
            ], 500);
        }
    }
}
