<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\students;
use App\Models\events;
use App\Models\tbl_attendance;
use App\Models\events_list_of_participants;
use App\Models\events_assign_participants;
use App\Models\attendance_payments;
use App\Models\semester;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardStudentController extends Controller
{
    public function index()
    {
        // Get authenticated student (from students guard)
        $student = Auth::guard('students')->user();
        
        if (!$student) {
            return redirect()->route('login.index')->with('error', 'Student profile not found.');
        }
        
        // Get active semester
        $activeSemester = semester::where('status', 'active')->first();
        
        // Get student's registered events
        $registeredAssignmentIds = events_list_of_participants::whereHas('events_assign_participants', function($q) use ($activeSemester) {
                if ($activeSemester) {
                    $q->where('semester_id', $activeSemester->id);
                }
            })
            ->where('students_id', $student->id)
            ->pluck('events_assign_participants_id')
            ->unique();
        
        // Get event IDs from the assignment IDs
        $eventIds = events_assign_participants::whereIn('id', $registeredAssignmentIds)
            ->pluck('events_id')
            ->unique();
        
        // Statistics
        $stats = [
            'total_events' => $eventIds->count(),
            'total_attendance' => tbl_attendance::where('student_id', $student->id)
                ->whereIn('event_id', $eventIds)
                ->count(),
            'total_fines' => attendance_payments::where('students_id', $student->id)
                ->whereIn('events_id', $eventIds)
                ->sum('amount_paid'),
            'paid_fines' => attendance_payments::where('students_id', $student->id)
                ->where('payment_status', 'paid')
                ->whereIn('events_id', $eventIds)
                ->sum('amount_paid'),
        ];
        
        $stats['unpaid_fines'] = $stats['total_fines'] - $stats['paid_fines'];
        
        // Recent attendance (last 10)
        $recentAttendances = tbl_attendance::where('student_id', $student->id)
            ->with(['event'])
            ->orderBy('log_time', 'desc')
            ->limit(10)
            ->get()
            ->map(function($attendance) {
                return [
                    'event_name' => $attendance->event ? $attendance->event->event_name : 'N/A',
                    'date_formatted' => $attendance->log_time ? Carbon::parse($attendance->log_time)->format('M d, Y') : '-',
                    'time_formatted' => $attendance->log_time ? Carbon::parse($attendance->log_time)->format('h:i A') : '-',
                    'workstate_text' => $attendance->workstate == 0 ? 'Time In' : 'Time Out',
                    'is_time_in' => $attendance->workstate == 0,
                ];
            });
        
        // Upcoming events
        $upcomingEvents = events::whereIn('id', $eventIds)
            ->where('status', 'active')
            ->where(function($q) {
                $q->where('start_datetime_morning', '>=', now())
                  ->orWhere('start_datetime_afternoon', '>=', now());
            })
            ->orderBy('start_datetime_morning')
            ->orderBy('start_datetime_afternoon')
            ->limit(5)
            ->get()
            ->map(function($event) {
                $startDate = $event->start_datetime_morning ?? $event->start_datetime_afternoon;
                return [
                    'event_name' => $event->event_name,
                    'event_description' => $event->event_description,
                    'date_formatted' => $startDate ? Carbon::parse($startDate)->format('M d, Y') : '-',
                    'time_formatted' => $startDate ? Carbon::parse($startDate)->format('h:i A') : '-',
                    'schedule_type' => $event->event_schedule_type,
                ];
            });
        
        // Recent payments
        $recentPayments = attendance_payments::where('students_id', $student->id)
            ->with(['events'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($payment) {
                return [
                    'event_name' => $payment->events ? $payment->events->event_name : 'N/A',
                    'amount' => $payment->amount_paid ?? 0,
                    'status' => $payment->payment_status,
                    'date_formatted' => Carbon::parse($payment->created_at)->format('M d, Y'),
                ];
            });
        
        return view('dashboard.dashboard-student', compact(
            'student',
            'stats',
            'recentAttendances',
            'upcomingEvents',
            'recentPayments',
            'activeSemester'
        ));
    }
}
