@extends('layouts.master')

@section('subcontent')
<h2 class="intro-y text-lg font-medium mt-10">
    Events
</h2>
<div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
    <div class="hidden md:block mx-auto text-slate-500">Showing {{ $events->count() }} entries</div>
    <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0 flex items-center gap-2">
        <a href="{{ route('print.list-of-events-pdf') }}" class="btn btn-primary shadow-md" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" icon-name="printer" data-lucide="printer" class="lucide lucide-printer w-4 h-4 mr-2">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Print PDF
        </a>
    </div>
</div>
<!-- BEGIN: Data List -->
<div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
    <table class="table table-report -mt-2" style="width: 100%;">
        <thead>
            <tr>
                <th class="whitespace-nowrap">EVENT NAME</th>
                <th class="whitespace-nowrap">DESCRIPTION</th>
                <th class="text-center whitespace-nowrap">SEMESTER</th>
                <th class="text-center whitespace-nowrap">SCHEDULE TYPE</th>
                <th class="text-center whitespace-nowrap">DATE/TIME</th>
                <th class="text-center whitespace-nowrap">FINES</th>
                <th class="text-center whitespace-nowrap">PARTICIPANTS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $event)
            <tr class="intro-x">
                <td>
                    <a href="" class="font-medium whitespace-nowrap">{{ $event->event_name }}</a>
                </td>
                <td>
                    <div class="text-slate-500 text-xs">{{ $event->event_description ?? 'N/A' }}</div>
                </td>
                <td class="text-center">
                    <div class="text-xs whitespace-nowrap">{{ $event->semester ? $event->semester->semester : 'N/A' }}</div>
                </td>
                <td class="text-center">
                    <div class="text-xs whitespace-nowrap">
                        @if($event->event_schedule_type == 'whole_day')
                            Whole Day
                        @elseif($event->event_schedule_type == 'half_day_morning')
                            Half Day (Morning)
                        @elseif($event->event_schedule_type == 'half_day_afternoon')
                            Half Day (Afternoon)
                        @else
                            N/A
                        @endif
                    </div>
                </td>
                <td class="text-center">
                    <div class="text-xs whitespace-nowrap">
                        @if($event->event_schedule_type == 'whole_day')
                            {{ $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning)->format('M d, Y') : 'N/A' }}
                        @elseif($event->event_schedule_type == 'half_day_morning')
                            {{ $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning)->format('M d, Y h:i A') : 'N/A' }}
                        @elseif($event->event_schedule_type == 'half_day_afternoon')
                            {{ $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon)->format('M d, Y h:i A') : 'N/A' }}
                        @endif
                    </div>
                </td>
                <td class="text-center">₱{{ number_format($event->fines ?? 0, 2) }}</td>
                <td class="text-center">
                    <span class="font-medium">{{ $event->participants_count }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-8 text-slate-500">No events found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<!-- END: Data List -->

<!-- BEGIN: Toast Component -->
@include('components.toast')
<!-- END: Toast Component -->
@endsection
@push('scripts')
<script src="{{ asset('js/reports/print-list-of-events.js') }}?v={{ time() }}"></script>
@endpush
