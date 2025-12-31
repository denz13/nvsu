<?php

namespace App\Http\Controllers\print;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\students;
use App\Models\college;
use App\Models\program;
use App\Models\organization;
use App\Models\events;
use App\Models\semester;
use App\Models\attendance_payments;
use App\Models\events_assign_participants;
use App\Models\events_list_of_participants;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintController extends Controller
{
    //
    public function listOfStudents()
    {
        $students = students::with(['college', 'program', 'organization'])->orderBy('created_at', 'desc')->get();
        $colleges = college::orderBy('college_name', 'asc')->get();
        $programs = program::orderBy('program_name', 'asc')->get();
        $organizations = organization::orderBy('organization_name', 'asc')->get();
        return view('reports.print-list-of-students', compact('students', 'colleges', 'programs', 'organizations'));
    }

    public function printListOfStudentsPDF()
    {
        $students = students::with(['college', 'program', 'organization'])->orderBy('created_at', 'desc')->get();
        
        $pdf = Pdf::loadView('reports.pdf-list-of-students', compact('students'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->stream('list-of-students-' . date('Y-m-d') . '.pdf');
    }

    public function listOfEvents()
    {
        $events = events::with(['semester'])->orderBy('created_at', 'desc')->get();
        return view('reports.print-list-of-events', compact('events'));
    }

    public function printListOfEventsPDF()
    {
        $events = events::with(['semester'])->orderBy('created_at', 'desc')->get();
        
        $pdf = Pdf::loadView('reports.pdf-list-of-events', compact('events'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->stream('list-of-events-' . date('Y-m-d') . '.pdf');
    }

    public function listOfPayments(Request $request)
    {
        $colleges = college::orderBy('college_name', 'asc')->get();
        $events = events::with('semester')->orderBy('created_at', 'desc')->get();
        $semesters = semester::orderBy('created_at', 'desc')->get();
        
        $selectedCollege = null;
        $selectedEvent = null;
        $selectedSemester = null;
        $reportData = null;
        
        if ($request->has('college_id') && $request->has('event_id') && $request->has('semester_id')) {
            $selectedCollege = college::find($request->college_id);
            $selectedEvent = events::find($request->event_id);
            $selectedSemester = semester::find($request->semester_id);
            
            if ($selectedCollege && $selectedEvent && $selectedSemester) {
                $reportData = $this->generateFinancialReportData($selectedCollege, $selectedEvent, $selectedSemester);
            }
        }
        
        return view('reports.print-list-of-payments', compact(
            'colleges', 
            'events', 
            'semesters',
            'selectedCollege', 
            'selectedEvent', 
            'selectedSemester',
            'reportData'
        ));
    }

    public function printListOfPaymentsPDF(Request $request)
    {
        $selectedCollege = college::find($request->college_id);
        $selectedEvent = events::find($request->event_id);
        $selectedSemester = semester::find($request->semester_id);
        
        $reportData = $this->generateFinancialReportData($selectedCollege, $selectedEvent, $selectedSemester);
        
        $pdf = Pdf::loadView('reports.pdf-list-of-payments', compact(
            'selectedCollege',
            'selectedEvent',
            'selectedSemester',
            'reportData'
        ));
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->stream('financial-report-' . date('Y-m-d') . '.pdf');
    }


    private function generateFinancialReportData($college, $event, $semester)
    {
        // Get assignment IDs for this event and semester
        $assignmentIds = events_assign_participants::where('events_id', $event->id)
            ->where('semester_id', $semester->id)
            ->where('status', 'active')
            ->pluck('id');
        
        // If no assignments found, return empty data
        if ($assignmentIds->isEmpty()) {
            return [
                'programs' => [],
                'total_participants' => 0,
                'total_fined_participants' => 0,
                'total_collected' => 0,
                'total_uncollected' => 0
            ];
        }
        
        // Get ALL students from this college (regardless of program status)
        $allStudentIds = students::where('college_id', $college->id)
            ->where('status', 'active')
            ->pluck('id');
        
        // Get all payments for this event for students in this college
        $allPayments = attendance_payments::whereIn('students_id', $allStudentIds)
            ->where('events_id', $event->id)
            ->where('status', 'active')
            ->whereHas('timeSchedules', function($query) {
                $query->where('status', 'active');
            })
            ->with('students.program')
            ->get();
        
        // Group payments by program and calculate totals
        $programGroups = $allPayments->groupBy(function($payment) {
            return $payment->students && $payment->students->program 
                ? $payment->students->program->id 
                : 'no_program';
        });
        
        $programData = [];
        $totalParticipants = 0;
        $totalFinedParticipants = 0;
        $totalCollected = 0;
        $totalUncollected = 0;
        
        foreach ($programGroups as $programId => $payments) {
            if ($programId === 'no_program') continue;
            
            // Get program name from first payment
            $programName = $payments->first()->students && $payments->first()->students->program 
                ? $payments->first()->students->program->program_name 
                : 'Unknown Program';
            
            // Get students in this program for participants count
            $studentIds = students::where('program_id', $programId)
                ->where('college_id', $college->id)
                ->where('status', 'active')
                ->pluck('id');
            
            // Get participants count
            $participantsCount = events_list_of_participants::whereIn('events_assign_participants_id', $assignmentIds)
                ->whereIn('students_id', $studentIds)
                ->where('status', 'active')
                ->count();
            
            // Count unique fined participants
            $finedCount = $payments->unique('students_id')->count();
            
            // COLLECTED: payment_status = 'approved'
            $collected = $payments->where('payment_status', 'approved')->sum('amount_paid');
            
            // UNCOLLECTED: payment_status = 'pending' or 'declined'
            $uncollected = $payments->whereIn('payment_status', ['pending', 'declined'])->sum('amount_paid');
            
            $programData[] = [
                'program_name' => $programName,
                'participants' => $participantsCount,
                'fined_participants' => $finedCount,
                'total_fines' => $collected + $uncollected
            ];
            
            $totalParticipants += $participantsCount;
            $totalFinedParticipants += $finedCount;
            $totalCollected += $collected;
            $totalUncollected += $uncollected;
        }
        
        return [
            'programs' => $programData,
            'total_participants' => $totalParticipants,
            'total_fined_participants' => $totalFinedParticipants,
            'total_collected' => $totalCollected,
            'total_uncollected' => $totalUncollected
        ];
    }
}
