<?php

namespace App\Http\Controllers\events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\events;
use App\Models\semester;
use App\Models\college;
use App\Models\program;
use App\Models\organization;
use App\Models\students;
use App\Models\events_assign_participants;
use App\Models\events_list_of_participants;
use App\Models\events_lates_deduction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EventsController extends Controller
{
    public function addEvent()
    {
        $events = events::orderBy('created_at', 'desc')->paginate(10);
        return view('events.add-event', compact('events'));
    }

    public function getEventDetails($id)
    {
        try {
            $event = events::findOrFail($id);
            $activeSemester = semester::find($event->semester_id);
            $assignmentModels = events_assign_participants::where('events_id', $id)
                ->with(['college','program','organization'])
                ->orderBy('id','desc')
                ->get();

            $assignmentIds = $assignmentModels->pluck('id');
            $participantsCount = events_list_of_participants::whereIn('events_assign_participants_id', $assignmentIds)->count();

            // Build readable assignments list with counts
            $assignments = $assignmentModels->map(function($a) {
                $count = events_list_of_participants::where('events_assign_participants_id', $a->id)->count();
                return [
                    'id' => $a->id,
                    'college' => $a->college ? ($a->college->college_name ?? '-') : 'All',
                    'program' => $a->program ? ($a->program->program_name ?? '-') : 'All',
                    'organization' => $a->organization ? ($a->organization->organization_name ?? '-') : 'None',
                    'status' => $a->status,
                    'participants' => $count,
                ];
            });

            // Participants list using the student's own college/program/organization details
            $participants = events_list_of_participants::whereIn('events_assign_participants_id', $assignmentIds)
                ->with(['students.college','students.program','students.organization'])
                ->orderBy('id','desc')
                ->get()
                ->map(function($p) {
                    return [
                        'student_id' => $p->students ? $p->students->id : null,
                        'student_name' => $p->students ? $p->students->student_name : 'N/A',
                        'id_number' => $p->students ? $p->students->id_number : 'N/A',
                        'college' => $p->students && $p->students->college ? ($p->students->college->college_name ?? '-') : '-',
                        'program' => $p->students && $p->students->program ? ($p->students->program->program_name ?? '-') : '-',
                        'organization' => $p->students && $p->students->organization ? ($p->students->organization->organization_name ?? '-') : '-',
                    ];
                });
            $late = events_lates_deduction::where('events_id', $id)->orderBy('id','desc')->first();
            
            return response()->json([
                'success' => true,
                'event' => $event,
                'semester' => $activeSemester,
                'participants_count' => $participantsCount,
                'assignments' => $assignments,
                'participants' => $participants,
                'late' => $late,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load details: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Load data for Add Participants modal
    public function getParticipantsFormData($id)
    {
        try {
            DB::beginTransaction();
            $event = events::findOrFail($id);
            $activeSemester = semester::where('status', 'active')->orderBy('id', 'desc')->first();
            
            // Use fallback empty collections if tables are empty to avoid errors
            $colleges = college::orderBy('college_name', 'asc')->get(['id','college_name']);
            $programs = program::orderBy('program_name', 'asc')->get(['id','program_name']);
            $organizations = organization::orderBy('organization_name', 'asc')->get(['id','organization_name']);
            
            // Normalize to {id, text}
            $colleges = $colleges->map(function($c){ return ['id' => $c->id, 'text' => $c->college_name]; });
            $programs = $programs->map(function($p){ return ['id' => $p->id, 'text' => $p->program_name]; });
            $organizations = $organizations->map(function($o){ return ['id' => $o->id, 'text' => $o->organization_name]; });
            
            // Include college_id/program_id for client-side filtering
            $studentList = students::orderBy('student_name','asc')->limit(1000)
                ->get(['id','student_name','id_number','college_id','program_id']);

            // Pull latest assignment defaults for this event
            $latestAssignment = events_assign_participants::where('events_id', $id)
                ->orderBy('id', 'desc')
                ->first();

            // Pull latest late rule defaults for this event
            $latestLate = events_lates_deduction::where('events_id', $id)
                ->orderBy('id', 'desc')
                ->first();
            
            return response()->json([
                'success' => true,
                'event' => $event,
                'semester' => $activeSemester,
                'colleges' => $colleges,
                'programs' => $programs,
                'organizations' => $organizations,
                'students' => $studentList,
                'defaults' => [
                    'college_id' => $latestAssignment ? $latestAssignment->college_id : null,
                    'program_id' => $latestAssignment ? $latestAssignment->program_id : null,
                    'organization_id' => $latestAssignment ? $latestAssignment->organization_id : null,
                    'time_in_morning' => $latestLate ? $latestLate->time_in_morning : null,
                    'time_out_morning' => $latestLate ? $latestLate->time_out_morning : null,
                    'time_in_afternoon' => $latestLate ? $latestLate->time_in_afternoon : null,
                    'time_out_afternoon' => $latestLate ? $latestLate->time_out_afternoon : null,
                    'late_penalty' => $latestLate ? $latestLate->late_penalty : null,
                ],
            ]);
        } catch (\Throwable $e) {
            // Return JSON error for easier debugging on the client
            return response()->json([
                'success' => false,
                'message' => 'Failed to load participants data: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Save Participants configuration
    public function saveParticipants(Request $request)
    {
        $request->validate([
            'events_id' => 'required|exists:events,id',
            // Allow empty string for "All" selections
            'college_id' => 'nullable',
            'program_id' => 'nullable',
            // Allow "none" or empty for organization
            'organization_id' => 'nullable',
            'students' => 'nullable|array',
            'students.*' => 'exists:students,id',
            'time_in_morning' => 'nullable|date_format:H:i',
            'time_out_morning' => 'nullable|date_format:H:i',
            'time_in_afternoon' => 'nullable|date_format:H:i',
            'time_out_afternoon' => 'nullable|date_format:H:i',
            'late_penalty' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive'
        ]);
        
        try {
            $activeSemester = semester::where('status', 'active')->orderBy('id', 'desc')->first();
            
            // Create or update assignment header (update latest existing for this event if present)
            $assign = events_assign_participants::where('events_id', $request->events_id)
                ->orderBy('id', 'desc')
                ->first();
            if (!$assign) {
                $assign = new events_assign_participants();
                $assign->events_id = $request->events_id;
            }
            $assign->college_id = $request->college_id !== '' ? $request->college_id : null;
            $assign->program_id = $request->program_id !== '' ? $request->program_id : null;
            $assign->organization_id = ($request->organization_id === 'none' || $request->organization_id === '') ? null : $request->organization_id;
            $assign->semester_id = $activeSemester ? $activeSemester->id : $assign->semester_id;
            $assign->status = $request->status ?? ($assign->status ?? 'active');
            $assign->save();
            
            // Save list of students: use provided list or auto-select by filters
            $studentIds = [];
            if ($request->filled('students')) {
                $studentIds = $request->students;
            } else {
                // Auto-pick students based on college/program/organization filters
                $studentsQuery = students::query();
                if ($request->has('college_id') && $request->college_id !== '') {
                    $studentsQuery->where('college_id', $request->college_id);
                }
                if ($request->has('program_id') && $request->program_id !== '') {
                    $studentsQuery->where('program_id', $request->program_id);
                }
                if ($request->has('organization_id') && Schema::hasColumn('students', 'organization_id')) {
                    if ($request->organization_id === 'none') {
                        $studentsQuery->whereNull('organization_id');
                    } elseif ($request->organization_id !== '') {
                        $studentsQuery->where('organization_id', $request->organization_id);
                    }
                }
                $studentIds = $studentsQuery->pluck('id')->toArray();
            }
            // If still empty (no filters and no explicit list), include all students
            if (empty($studentIds)) {
                $studentIds = students::pluck('id')->toArray();
            }

            // Sync participants: delete removed, insert new
            $existing = events_list_of_participants::where('events_assign_participants_id', $assign->id)
                ->pluck('students_id')
                ->all();
            $existingMap = array_fill_keys($existing, true);
            $incomingMap = array_fill_keys($studentIds, true);
            // Delete those not in new list
            if (!empty($existing)) {
                events_list_of_participants::where('events_assign_participants_id', $assign->id)
                    ->whereNotIn('students_id', $studentIds)
                    ->delete();
            }
            // Insert missing
            $toInsert = [];
            foreach ($studentIds as $studentId) {
                if (!isset($existingMap[$studentId])) {
                    $toInsert[] = [
                        'events_assign_participants_id' => $assign->id,
                        'students_id' => $studentId,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if (!empty($toInsert)) {
                events_list_of_participants::insert($toInsert);
            }
            
            // Save or update late deduction config if provided
            $hasTimeFields = $request->filled('time_in_morning') || $request->filled('time_out_morning') || 
                            $request->filled('time_in_afternoon') || $request->filled('time_out_afternoon') || 
                            $request->filled('late_penalty');
            
            if ($hasTimeFields) {
                $late = events_lates_deduction::where('events_id', $request->events_id)
                    ->orderBy('id', 'desc')
                    ->first();
                if (!$late) {
                    $late = new events_lates_deduction();
                    $late->events_id = $request->events_id;
                }
                
                // Save time fields directly (form already sends the correct field names)
                if ($request->filled('time_in_morning')) {
                    $late->time_in_morning = $request->time_in_morning;
                }
                if ($request->filled('time_out_morning')) {
                    $late->time_out_morning = $request->time_out_morning;
                }
                if ($request->filled('time_in_afternoon')) {
                    $late->time_in_afternoon = $request->time_in_afternoon;
                }
                if ($request->filled('time_out_afternoon')) {
                    $late->time_out_afternoon = $request->time_out_afternoon;
                }
                
                $late->late_penalty = $request->late_penalty ?? ($late->late_penalty ?? 0);
                $late->semester_id = $activeSemester ? $activeSemester->id : $late->semester_id;
                $late->status = 'active';
                $late->save();
            }
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Participants configuration saved successfully.',
                'updated' => true,
                'total_selected' => count($studentIds)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save participants: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $scheduleType = $request->event_schedule_type;
        
        // Base validation
        $rules = [
            'event_name' => 'required|string|max:255',
            'event_description' => 'nullable|string',
            'fines' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'event_schedule_type' => 'required|in:whole_day,half_day_morning,half_day_afternoon'
        ];
        
        // Add datetime validation based on schedule type
        if ($scheduleType === 'whole_day') {
            $rules['start_datetime_morning'] = 'required|date';
            $rules['end_datetime_morning'] = 'required|date|after:start_datetime_morning';
            $rules['start_datetime_afternoon'] = 'required|date|after:end_datetime_morning';
            $rules['end_datetime_afternoon'] = 'required|date|after:start_datetime_afternoon';
        } elseif ($scheduleType === 'half_day_morning') {
            $rules['start_datetime_morning'] = 'required|date';
            $rules['end_datetime_morning'] = 'required|date|after:start_datetime_morning';
        } elseif ($scheduleType === 'half_day_afternoon') {
            $rules['start_datetime_afternoon'] = 'required|date';
            $rules['end_datetime_afternoon'] = 'required|date|after:start_datetime_afternoon';
        }
        
        // Validate with custom messages
        $messages = [
            'start_datetime_morning.required' => 'Morning start date & time is required.',
            'end_datetime_morning.required' => 'Morning end date & time is required.',
            'end_datetime_morning.after' => 'Morning end time must be after morning start time.',
            'start_datetime_afternoon.required' => 'Afternoon start date & time is required.',
            'start_datetime_afternoon.after' => 'Afternoon start time must be after morning end time.',
            'end_datetime_afternoon.required' => 'Afternoon end date & time is required.',
            'end_datetime_afternoon.after' => 'Afternoon end time must be after afternoon start time.',
        ];
        
        $request->validate($rules, $messages);
        
        try {
            // Auto-assign current active semester (hidden from modal/UI)
            $activeSemester = semester::where('status', 'active')->orderBy('id', 'desc')->first();
            
            $event = new events();
            $event->semester_id = $activeSemester ? $activeSemester->id : null;
            $event->event_name = $request->event_name;
            $event->event_description = $request->event_description;
            $event->event_schedule_type = $request->event_schedule_type;
            $event->fines = $request->fines;
            $event->status = $request->status;
            
            // Set datetime fields based on schedule type
            if ($scheduleType === 'whole_day') {
                $event->start_datetime_morning = $request->start_datetime_morning;
                $event->end_datetime_morning = $request->end_datetime_morning;
                $event->start_datetime_afternoon = $request->start_datetime_afternoon;
                $event->end_datetime_afternoon = $request->end_datetime_afternoon;
            } elseif ($scheduleType === 'half_day_morning') {
                $event->start_datetime_morning = $request->start_datetime_morning;
                $event->end_datetime_morning = $request->end_datetime_morning;
                $event->start_datetime_afternoon = null;
                $event->end_datetime_afternoon = null;
            } elseif ($scheduleType === 'half_day_afternoon') {
                $event->start_datetime_morning = null;
                $event->end_datetime_morning = null;
                $event->start_datetime_afternoon = $request->start_datetime_afternoon;
                $event->end_datetime_afternoon = $request->end_datetime_afternoon;
            }
            
            $event->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Event added successfully!',
                'data' => $event
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add event: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function edit($id)
    {
        $event = events::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $event
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $scheduleType = $request->event_schedule_type;
        
        // Base validation
        $rules = [
            'event_name' => 'required|string|max:255',
            'event_description' => 'nullable|string',
            'fines' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'event_schedule_type' => 'required|in:whole_day,half_day_morning,half_day_afternoon'
        ];
        
        // Add datetime validation based on schedule type
        if ($scheduleType === 'whole_day') {
            $rules['start_datetime_morning'] = 'required|date';
            $rules['end_datetime_morning'] = 'required|date|after:start_datetime_morning';
            $rules['start_datetime_afternoon'] = 'required|date|after:end_datetime_morning';
            $rules['end_datetime_afternoon'] = 'required|date|after:start_datetime_afternoon';
        } elseif ($scheduleType === 'half_day_morning') {
            $rules['start_datetime_morning'] = 'required|date';
            $rules['end_datetime_morning'] = 'required|date|after:start_datetime_morning';
        } elseif ($scheduleType === 'half_day_afternoon') {
            $rules['start_datetime_afternoon'] = 'required|date';
            $rules['end_datetime_afternoon'] = 'required|date|after:start_datetime_afternoon';
        }
        
        // Validate with custom messages
        $messages = [
            'start_datetime_morning.required' => 'Morning start date & time is required.',
            'end_datetime_morning.required' => 'Morning end date & time is required.',
            'end_datetime_morning.after' => 'Morning end time must be after morning start time.',
            'start_datetime_afternoon.required' => 'Afternoon start date & time is required.',
            'start_datetime_afternoon.after' => 'Afternoon start time must be after morning end time.',
            'end_datetime_afternoon.required' => 'Afternoon end date & time is required.',
            'end_datetime_afternoon.after' => 'Afternoon end time must be after afternoon start time.',
        ];
        
        $request->validate($rules, $messages);
        
        try {
            $event = events::findOrFail($id);
            
            // Auto-assign current active semester on update as well (kept hidden from UI)
            $activeSemester = semester::where('status', 'active')->orderBy('id', 'desc')->first();
            $event->semester_id = $activeSemester ? $activeSemester->id : $event->semester_id;
            $event->event_name = $request->event_name;
            $event->event_description = $request->event_description;
            $event->event_schedule_type = $request->event_schedule_type;
            $event->fines = $request->fines;
            $event->status = $request->status;
            
            // Set datetime fields based on schedule type
            if ($scheduleType === 'whole_day') {
                $event->start_datetime_morning = $request->start_datetime_morning;
                $event->end_datetime_morning = $request->end_datetime_morning;
                $event->start_datetime_afternoon = $request->start_datetime_afternoon;
                $event->end_datetime_afternoon = $request->end_datetime_afternoon;
            } elseif ($scheduleType === 'half_day_morning') {
                $event->start_datetime_morning = $request->start_datetime_morning;
                $event->end_datetime_morning = $request->end_datetime_morning;
                $event->start_datetime_afternoon = null;
                $event->end_datetime_afternoon = null;
            } elseif ($scheduleType === 'half_day_afternoon') {
                $event->start_datetime_morning = null;
                $event->end_datetime_morning = null;
                $event->start_datetime_afternoon = $request->start_datetime_afternoon;
                $event->end_datetime_afternoon = $request->end_datetime_afternoon;
            }
            
            $event->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully!',
                'data' => $event
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update event: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $event = events::findOrFail($id);
            $event->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete event: ' . $e->getMessage()
            ], 500);
        }
    }
}
