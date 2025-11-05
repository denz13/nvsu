<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\students;
use App\Models\events;
use App\Models\User;
use App\Models\tbl_attendance;
use App\Models\college;
use App\Models\program;
use App\Models\organization;
use App\Models\announcement;

class DashboardController extends Controller
{
    public function dashboard()
    {
        // Get total counts from database
        $stats = [
            'total_students' => students::count(),
            'total_events' => events::count(),
            'total_users' => User::count(),
            'total_attendance' => tbl_attendance::count(),
            'total_colleges' => college::count(),
            'total_programs' => program::count(),
            'total_organizations' => organization::count(),
            'total_announcements' => announcement::count(),
        ];
        
        // Get previous month counts for percentage calculations
        $previousMonth = now()->subMonth();
        
        $stats['previous_month_students'] = students::where('created_at', '<', $previousMonth->startOfMonth())->count();
        $stats['previous_month_events'] = events::where('created_at', '<', $previousMonth->startOfMonth())->count();
        $stats['previous_month_users'] = User::where('created_at', '<', $previousMonth->startOfMonth())->count();
        $stats['previous_month_attendance'] = tbl_attendance::where('created_at', '<', $previousMonth->startOfMonth())->count();
        
        // Calculate percentage changes
        $stats['students_percentage'] = $this->calculatePercentageChange($stats['previous_month_students'], $stats['total_students']);
        $stats['events_percentage'] = $this->calculatePercentageChange($stats['previous_month_events'], $stats['total_events']);
        $stats['users_percentage'] = $this->calculatePercentageChange($stats['previous_month_users'], $stats['total_users']);
        $stats['attendance_percentage'] = $this->calculatePercentageChange($stats['previous_month_attendance'], $stats['total_attendance']);
        
        // Get recent attendance records for Transactions section (latest 5)
        $recentAttendances = tbl_attendance::with(['student', 'event'])
            ->orderBy('log_time', 'desc')
            ->limit(5)
            ->get()
            ->map(function($attendance) {
                // Get student name
                $studentName = 'Unknown';
                $studentPhoto = asset('dist/images/profile-5.jpg');
                
                if ($attendance->student) {
                    $studentName = $attendance->student->student_name;
                    if ($attendance->student->photo) {
                        $studentPhoto = asset('storage/' . str_replace('storage/', '', $attendance->student->photo));
                    }
                }
                
                // Get event name
                $eventName = 'Unknown Event';
                if ($attendance->event) {
                    $eventName = $attendance->event->event_name;
                }
                
                // Determine status based on workstate (0 = Time In, 1 or other = Time Out)
                $isTimeIn = ($attendance->workstate == "0" || $attendance->workstate == 0);
                $statusText = $isTimeIn ? 'Time In' : 'Time Out';
                
                return [
                    'id' => $attendance->id,
                    'student_name' => $studentName,
                    'student_photo' => $studentPhoto,
                    'event_name' => $eventName,
                    'log_time' => $attendance->log_time,
                    'workstate' => $attendance->workstate,
                    'workstate_text' => $statusText,
                    'status' => $attendance->status,
                    'is_time_in' => $isTimeIn,
                    'date_formatted' => $attendance->log_time ? $attendance->log_time->format('M d, Y') : '-',
                    'time_formatted' => $attendance->log_time ? $attendance->log_time->format('h:i A') : '-',
                ];
            });
        
        // Get recent events for Recent Activities section (latest 5)
        $recentEvents = events::orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($event) {
                // Format event date
                $eventDate = $event->created_at ? $event->created_at->format('M d, Y') : '-';
                $eventTime = $event->created_at ? $event->created_at->format('h:i A') : '-';
                
                // Determine event start date based on schedule type
                $startDate = '-';
                if ($event->event_schedule_type === 'whole_day' && $event->start_datetime_morning) {
                    $startDate = date('M d, Y', strtotime($event->start_datetime_morning));
                } elseif ($event->event_schedule_type === 'half_day_morning' && $event->start_datetime_morning) {
                    $startDate = date('M d, Y', strtotime($event->start_datetime_morning));
                } elseif ($event->event_schedule_type === 'half_day_afternoon' && $event->start_datetime_afternoon) {
                    $startDate = date('M d, Y', strtotime($event->start_datetime_afternoon));
                }
                
                return [
                    'id' => $event->id,
                    'event_name' => $event->event_name,
                    'event_description' => $event->event_description,
                    'status' => $event->status,
                    'created_at' => $event->created_at,
                    'date_formatted' => $eventDate,
                    'time_formatted' => $eventTime,
                    'start_date' => $startDate,
                ];
            });
        
        return view('dashboard.dashboard', compact('stats', 'recentAttendances', 'recentEvents'));
    }
    
    private function calculatePercentageChange($oldValue, $newValue)
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        
        $change = (($newValue - $oldValue) / $oldValue) * 100;
        return round($change, 1);
    }
}
