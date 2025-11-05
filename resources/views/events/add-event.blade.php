@extends('layouts.master')

@section('subcontent')
<h2 class="intro-y text-lg font-medium mt-10">
    Events
</h2>
<div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
    <button class="btn btn-primary shadow-md mr-2" data-tw-toggle="modal" data-tw-target="#add-product-modal">Add New Event</button>

    <div class="hidden md:block mx-auto text-slate-500">Showing {{ $events->firstItem() ?? 0 }} to {{ $events->lastItem() ?? 0 }} of {{ $events->total() }} entries</div>
    <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
        <div class="w-56 relative text-slate-500">
            <input type="text" class="form-control w-56 box pr-10" placeholder="Search...">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="search" class="lucide lucide-search w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </div>
    </div>
</div>
<!-- BEGIN: HTML Table Data -->
<div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
    <table class="table table-report -mt-2">
        <thead>
            <tr>
                <th class="whitespace-nowrap">EVENT NAME</th>
                <th class="text-center whitespace-nowrap">START DATETIME</th>
                <th class="text-center whitespace-nowrap">END DATETIME</th>
                <th class="text-center whitespace-nowrap">FINES</th>
                <th class="text-center whitespace-nowrap">STATUS</th>
                <th class="text-center whitespace-nowrap">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $event)
            <tr class="intro-x">
                <td>
                    <a href="" class="font-medium whitespace-nowrap">{{ $event->event_name }}</a>
                    <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ $event->event_description }}</div>
                </td>
                <td class="text-center">
                    @if($event->event_schedule_type === 'whole_day')
                        {{ $event->start_datetime_morning ? date('M d, Y h:i A', strtotime($event->start_datetime_morning)) : '-' }}
                    @elseif($event->event_schedule_type === 'half_day_morning')
                        {{ $event->start_datetime_morning ? date('M d, Y h:i A', strtotime($event->start_datetime_morning)) : '-' }}
                    @elseif($event->event_schedule_type === 'half_day_afternoon')
                        {{ $event->start_datetime_afternoon ? date('M d, Y h:i A', strtotime($event->start_datetime_afternoon)) : '-' }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">
                    @if($event->event_schedule_type === 'whole_day')
                        {{ $event->end_datetime_afternoon ? date('M d, Y h:i A', strtotime($event->end_datetime_afternoon)) : '-' }}
                    @elseif($event->event_schedule_type === 'half_day_morning')
                        {{ $event->end_datetime_morning ? date('M d, Y h:i A', strtotime($event->end_datetime_morning)) : '-' }}
                    @elseif($event->event_schedule_type === 'half_day_afternoon')
                        {{ $event->end_datetime_afternoon ? date('M d, Y h:i A', strtotime($event->end_datetime_afternoon)) : '-' }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">₱{{ number_format($event->fines, 2) }}</td>
                <td class="w-40">
                    @if($event->status === 'active')
                    <div class="flex items-center justify-center text-success"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="check-square" data-lucide="check-square" class="lucide lucide-check-square w-4 h-4 mr-2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path></svg> Active </div>
                    @else
                    <div class="flex items-center justify-center text-danger"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="check-square" data-lucide="check-square" class="lucide lucide-check-square w-4 h-4 mr-2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path></svg> Inactive </div>
                    @endif
                </td>
                <td class="table-report__action w-56">
                    <div class="flex justify-center items-center">
                        <a class="flex items-center mr-3 text-primary tooltip" href="javascript:;" onclick="addParticipants('{{ $event->id }}')" data-id="{{ $event->id }}" title="Add Participants">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="user-plus" data-lucide="user-plus" class="lucide lucide-user-plus w-4 h-4"></svg>
                        </a>
                        <a class="flex items-center mr-3 tooltip" href="javascript:;" onclick="editEvent('{{ $event->id }}')" data-id="{{ $event->id }}" title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="check-square" data-lucide="check-square" class="lucide lucide-check-square w-4 h-4"></svg>
                        </a>
                        <a class="flex items-center mr-3 tooltip" href="javascript:;" onclick="viewEvent('{{ $event->id }}')" data-id="{{ $event->id }}" title="View Details">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="eye" data-lucide="eye" class="lucide lucide-eye w-4 h-4"></svg>
                        </a>
                        <a class="flex items-center text-danger tooltip" href="javascript:;" onclick="confirmDelete('{{ $event->id }}', '{{ $event->event_name }}')" data-id="{{ $event->id }}" title="Delete">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="trash-2" data-lucide="trash-2" class="lucide lucide-trash-2 w-4 h-4"></svg>
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- END: HTML Table Data -->
<!-- BEGIN: Pagination -->
{{ $events->links('components.pagination') }}
<!-- END: Pagination -->

<!-- BEGIN: Toast Component -->
@include('components.toast')
<!-- END: Toast Component -->

<!-- BEGIN: Add Product Modal -->
@include('components.modal', [
'modalId' => 'add-product-modal',
'size' => 'xl',
'title' => 'Add New Event',
'body' => '
<form id="add-product-form">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="form-label">Event Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="event_name" placeholder="Enter event name" required>
        </div>
        <div class="md:col-span-2">
            <label class="form-label">Event Description</label>
            <textarea class="form-control" name="event_description" rows="3" placeholder="Enter event description"></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="form-label">Event Schedule Type <span class="text-danger">*</span></label>
            <select class="form-control" id="add-event-schedule-type" name="event_schedule_type" required>
                <option value="">-- Select Schedule Type --</option>
                <option value="whole_day">Whole Day</option>
                <option value="half_day_morning">Half day Morning</option>
                <option value="half_day_afternoon">Half day Afternoon</option>
            </select>
        </div>
        <div class="md:col-span-1" id="add-morning-start-group" style="display: none;">
            <label class="form-label">Morning Start Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control w-full" id="add-start-datetime-morning" name="start_datetime_morning" style="width: 100%;">
        </div>
        <div class="md:col-span-1" id="add-morning-end-group" style="display: none;">
            <label class="form-label">Morning End Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control w-full" id="add-end-datetime-morning" name="end_datetime_morning" style="width: 100%;">
        </div>
        <div class="md:col-span-1" id="add-afternoon-start-group" style="display: none;">
            <label class="form-label">Afternoon Start Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control w-full" id="add-start-datetime-afternoon" name="start_datetime_afternoon" style="width: 100%;">
        </div>
        <div class="md:col-span-1" id="add-afternoon-end-group" style="display: none;">
            <label class="form-label">Afternoon End Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control w-full" id="add-end-datetime-afternoon" name="end_datetime_afternoon" style="width: 100%;">
        </div>
        <div class="md:col-span-2">
            <label class="form-label">Fines</label>
            <input type="number" class="form-control" name="fines" placeholder="0.00" step="0.01" min="0">
        </div>
        <div class="md:col-span-2">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <div class="flex items-center mt-2">
                <input type="radio" name="status" id="status-active" class="form-check-input" value="active" checked>
                <label for="status-active" class="ml-2 mr-5">Active</label>
                <input type="radio" name="status" id="status-inactive" class="form-check-input" value="inactive">
                <label for="status-inactive" class="ml-2">Inactive</label>
            </div>
        </div>
    </div>
</form>
',
'footer' => '
<button type="button" class="btn btn-secondary" data-tw-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="save-product-btn">
    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Event
</button>
',
'showButton' => false
])
<!-- END: Add Product Modal -->

<!-- BEGIN: Edit Product Modal -->
@include('components.modal', [
'modalId' => 'edit-product-modal',
'size' => 'xl',
'title' => 'Edit Event',
'body' => '
<form id="edit-product-form">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="form-label">Event Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit-event-name" name="event_name" placeholder="Enter event name" required>
        </div>
        <div class="md:col-span-2">
            <label class="form-label">Event Description</label>
            <textarea class="form-control" id="edit-event-description" name="event_description" rows="3" placeholder="Enter event description"></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="form-label">Event Schedule Type <span class="text-danger">*</span></label>
            <select class="form-control" id="edit-event-schedule-type" name="event_schedule_type" required>
                <option value="">-- Select Schedule Type --</option>
                <option value="whole_day">Whole Day</option>
                <option value="half_day_morning">Half day Morning</option>
                <option value="half_day_afternoon">Half day Afternoon</option>
            </select>
        </div>
        <div class="md:col-span-1" id="edit-morning-start-group" style="display: none;">
            <label class="form-label">Morning Start Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control w-full" id="edit-start-datetime-morning" name="start_datetime_morning" style="width: 100%;">
        </div>
        <div class="md:col-span-1" id="edit-morning-end-group" style="display: none;">
            <label class="form-label">Morning End Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control w-full" id="edit-end-datetime-morning" name="end_datetime_morning" style="width: 100%;">
        </div>
        <div class="md:col-span-1" id="edit-afternoon-start-group" style="display: none;">
            <label class="form-label">Afternoon Start Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control w-full" id="edit-start-datetime-afternoon" name="start_datetime_afternoon" style="width: 100%;">
        </div>
        <div class="md:col-span-1" id="edit-afternoon-end-group" style="display: none;">
            <label class="form-label">Afternoon End Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control w-full" id="edit-end-datetime-afternoon" name="end_datetime_afternoon" style="width: 100%;">
        </div>
        <div class="md:col-span-2">
            <label class="form-label">Fines</label>
            <input type="number" class="form-control" id="edit-fines" name="fines" placeholder="0.00" step="0.01" min="0">
        </div>
        <div class="md:col-span-2">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <div class="flex items-center mt-2">
                <input type="radio" name="status" id="edit-status-active" class="form-check-input" value="active">
                <label for="edit-status-active" class="ml-2 mr-5">Active</label>
                <input type="radio" name="status" id="edit-status-inactive" class="form-check-input" value="inactive">
                <label for="edit-status-inactive" class="ml-2">Inactive</label>
            </div>
        </div>
    </div>
</form>
',
'footer' => '
<button type="button" class="btn btn-secondary" data-tw-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="update-product-btn">
    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Update Event
</button>
<button type="button" class="btn btn-outline-primary ml-2" id="edit-participants-open-btn">
    <i data-lucide="user-plus" class="w-4 h-4 mr-2"></i> Edit Participants
</button>
',
'showButton' => false
])
<!-- END: Edit Product Modal -->

<!-- BEGIN: Delete Confirmation Modal -->
@include('components.modal', [
'modalId' => 'delete-confirmation-modal',
'size' => 'sm',
'title' => 'Delete Confirmation',
'body' => '
<div class="p-5 text-center">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="alert-triangle" data-lucide="alert-triangle" class="lucide lucide-alert-triangle w-16 h-16 text-danger mx-auto mt-3">
        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
        <path d="M12 9v4"></path>
        <path d="M12 17h.01"></path>
    </svg>
    <div class="text-slate-500 mt-4">Are you sure you want to delete this event?</div>
    <div class="text-danger font-medium mt-2" id="delete-event-name"></div>
</div>
',
'footer' => '
<button type="button" class="btn btn-secondary mr-2" data-tw-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-danger" id="confirm-delete-btn">
    <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Delete Event
</button>
',
'showButton' => false
])
<!-- END: Delete Confirmation Modal -->

<!-- BEGIN: Add Participants Modal -->
@include('components.modal', [
'modalId' => 'add-participants-modal',
'size' => 'lg',
'title' => 'Add Participants',
'body' => '
<form id="add-participants-form">
    <input type="hidden" id="ap-events-id" name="events_id" />
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label">College</label>
            <select id="ap-college" name="college_id" class="tom-select w-full" data-placeholder="Select college">
                <option value="">-- Select College --</option>
            </select>
        </div>
        <div>
            <label class="form-label">Program</label>
            <select id="ap-program" name="program_id" class="tom-select w-full" data-placeholder="Select program">
                <option value="">-- Select Program --</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="form-label">Organization</label>
            <select id="ap-organization" name="organization_id" class="tom-select w-full" data-placeholder="Select organization">
                <option value="">-- Select Organization --</option>
            </select>
        </div>
        
        <div id="ap-morning-time-in-group" style="display: none;">
            <label class="form-label">Morning Allowed Time In</label>
            <input type="time" class="form-control" id="ap-time-in-morning" name="time_in_morning">
        </div>
        <div id="ap-morning-time-out-group" style="display: none;">
            <label class="form-label">Morning Allowed Time Out</label>
            <input type="time" class="form-control" id="ap-time-out-morning" name="time_out_morning">
        </div>
        <div id="ap-afternoon-time-in-group" style="display: none;">
            <label class="form-label">Afternoon Allowed Time In</label>
            <input type="time" class="form-control" id="ap-time-in-afternoon" name="time_in_afternoon">
        </div>
        <div id="ap-afternoon-time-out-group" style="display: none;">
            <label class="form-label">Afternoon Allowed Time Out</label>
            <input type="time" class="form-control" id="ap-time-out-afternoon" name="time_out_afternoon">
        </div>
        <div class="md:col-span-2">
            <label class="form-label">Late Penalty</label>
            <input type="number" step="0.01" min="0" class="form-control" id="ap-late-penalty" name="late_penalty" placeholder="0.00">
        </div>
    </div>
</form>
',
'footer' => '
<button type="button" class="btn btn-secondary" data-tw-dismiss="modal">Close</button>
<button type="button" class="btn btn-primary" id="ap-save-btn">
    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Save
</button>
',
'showButton' => false
])
<!-- END: Add Participants Modal -->
 
<!-- BEGIN: View Event Modal -->
@include('components.modal', [
'modalId' => 'view-event-modal',
'size' => 'lg',
'title' => 'Event Details',
'body' => '
<div id="view-event-content" class="p-2">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <div class="text-xs text-slate-500">Event Name</div>
            <div class="font-medium" id="ve-name">-</div>
        </div>
        <div>
            <div class="text-xs text-slate-500">Semester</div>
            <div class="font-medium" id="ve-semester">-</div>
        </div>
        <div>
            <div class="text-xs text-slate-500">Start</div>
            <div class="font-medium" id="ve-start">-</div>
        </div>
        <div>
            <div class="text-xs text-slate-500">End</div>
            <div class="font-medium" id="ve-end">-</div>
        </div>
        <div>
            <div class="text-xs text-slate-500">Schedule Type</div>
            <div class="font-medium" id="ve-schedule-type">-</div>
        </div>
        <div>
            <div class="text-xs text-slate-500">Late Rule</div>
            <div class="font-medium" id="ve-late">-</div>
        </div>
        <div class="md:col-span-2">
            <div class="text-xs text-slate-500">Description</div>
            <div class="font-medium" id="ve-desc">-</div>
        </div>
        <div>
            <div class="text-xs text-slate-500">Participants</div>
            <div class="font-medium" id="ve-participants">0</div>
        </div>
        <div></div>
        <div class="md:col-span-2 mt-4">
            <div class="text-xs text-slate-500 mb-2">Assignments (College / Program / Organization)</div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>College</th>
                            <th>Program</th>
                            <th>Organization</th>
                            <th class="text-right">Participants</th>
                        </tr>
                    </thead>
                    <tbody id="ve-assignments">
                        <tr><td colspan="4" class="text-center text-slate-500">No assignments</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="md:col-span-2 mt-4">
            <div class="text-xs text-slate-500 mb-2">Participants</div>
            <div class="overflow-x-auto max-h-64 overflow-y-auto border border-slate-200 rounded-md">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>ID Number</th>
                            <th>College</th>
                            <th>Program</th>
                            <th>Organization</th>
                        </tr>
                    </thead>
                    <tbody id="ve-participants-body">
                        <tr><td colspan="5" class="text-center text-slate-500">No participants</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
',
'footer' => '
<button type="button" class="btn btn-secondary" data-tw-dismiss="modal">Close</button>
',
'showButton' => false
])
<!-- END: View Event Modal -->

@endsection
@push('scripts')
<script src="{{ asset('js/events/events.js') }}?v={{ time() }}"></script>
@endpush


