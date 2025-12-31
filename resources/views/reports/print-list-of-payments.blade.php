@extends('layouts.master')

@section('subcontent')
<h2 class="intro-y text-lg font-medium mt-10 whitespace-nowrap">
    Financial Report Per College
</h2>

<div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
    <form method="GET" action="{{ route('print.list-of-payments') }}" id="filterForm" class="flex gap-2 mr-2">
        <select id="college_id" name="college_id" class="form-select w-56 h-12" required>
            <option value="">Select College</option>
            @foreach($colleges as $college)
                <option value="{{ $college->id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>
                    {{ $college->college_name }}
                </option>
            @endforeach
        </select>
        
        <select id="event_id" name="event_id" class="form-select w-56 h-12" required>
            <option value="">Select Event</option>
            @foreach($events as $event)
                <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                    {{ $event->event_name }}
                </option>
            @endforeach
        </select>
        
        <select id="semester_id" name="semester_id" class="form-select w-56 h-12" required>
            <option value="">Select Semester</option>
            @foreach($semesters as $semester)
                <option value="{{ $semester->id }}" {{ request('semester_id') == $semester->id ? 'selected' : '' }}>
                    {{ $semester->semester }} SY {{ $semester->school_year }}
                </option>
            @endforeach
        </select>
        
        <button type="submit" class="btn btn-primary shadow-md">Generate Report</button>
    </form>

    <div class="hidden md:block mx-auto text-slate-500"></div>

    <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
        @if($reportData)
            <a href="{{ route('print.list-of-payments-pdf', request()->all()) }}" target="_blank" class="btn btn-primary shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="printer" data-lucide="printer" class="lucide lucide-printer w-4 h-4 mr-2">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Print PDF
            </a>
        @endif
    </div>
</div>

@if($reportData && $selectedCollege && $selectedEvent)
<!-- BEGIN: Data List -->
<div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
    <table class="table table-report -mt-2" style="width: 100%;">
        <thead>
            <tr>
                <th class="whitespace-nowrap">College</th>
                <th class="whitespace-nowrap">Event</th>
                <th class="whitespace-nowrap">Semester</th>
                <th class="whitespace-nowrap">Total Participants</th>
                <th class="whitespace-nowrap">Total Fines Collected</th>
                <th class="whitespace-nowrap">Total Fines Uncollected</th>
                <th class="whitespace-nowrap">Report Period Covered</th>
            </tr>
        </thead>
        <tbody>
            <tr class="intro-x">
                <td class="font-medium">{{ $selectedCollege->college_name }}</td>
                <td>{{ $selectedEvent->event_name }}</td>
                <td>{{ $selectedSemester ? $selectedSemester->semester : 'N/A' }}</td>
                <td class="text-center">{{ $reportData['total_participants'] ?? 0 }}</td>
                <td class="text-center text-success font-medium">₱{{ number_format($reportData['total_collected'], 2) }}</td>
                <td class="text-center text-danger font-medium">₱{{ number_format($reportData['total_uncollected'], 2) }}</td>
                <td>{{ $selectedSemester ? 'A.Y ' . $selectedSemester->school_year : 'N/A' }}</td>
            </tr>
        </tbody>
    </table>
</div>
<!-- END: Data List -->
@else
<!-- Empty State Message -->
<div class="intro-y col-span-12 overflow-auto lg:overflow-visible mt-5">
    <div class="box p-10 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-filter w-16 h-16 text-slate-400 mx-auto mb-4">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
        </svg>
        <h2 class="text-lg font-medium mb-2">No Report Data Available</h2>
        <p class="text-slate-500">Please select College, Event, and Semester filters above and click "Generate Report" to view the financial report data.</p>
    </div>
</div>
@endif

@include('components.toast')
@endsection

@push('scripts')
<script src="{{ asset('js/reports/print-list-of-payments.js') }}?v={{ time() }}"></script>
@endpush

