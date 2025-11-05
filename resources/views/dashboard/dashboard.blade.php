@extends('layouts.master')


@section('subcontent')
<div class="grid grid-cols-12 gap-6 col-span-12">
    <div class="col-span-12 2xl:col-span-9">
        <div class="grid grid-cols-12 gap-6">
            <!-- BEGIN: General Report -->
            <div class="col-span-12 mt-8">
                <div class="intro-y flex items-center h-10">
                    <h2 class="text-lg font-medium truncate mr-5">
                        General Report
                    </h2>
                    <a href="" class="ml-auto flex items-center text-primary"> <i data-lucide="refresh-ccw" class="w-4 h-4 mr-3"></i> Reload Data </a>
                </div>
                <div class="grid grid-cols-12 gap-6 mt-5">
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        <div class="report-box zoom-in">
                            <div class="box p-5">
                                <div class="flex">
                                    <i data-lucide="users" class="report-box__icon text-primary"></i>
                                    <div class="ml-auto">
                                        @if($stats['students_percentage'] >= 0)
                                        <div class="report-box__indicator bg-success tooltip cursor-pointer" title="{{ abs($stats['students_percentage']) }}% {{ $stats['students_percentage'] >= 0 ? 'Higher' : 'Lower' }} than last month">
                                            {{ abs($stats['students_percentage']) }}% <i data-lucide="chevron-up" class="w-4 h-4 ml-0.5"></i>
                                        </div>
                                        @else
                                        <div class="report-box__indicator bg-danger tooltip cursor-pointer" title="{{ abs($stats['students_percentage']) }}% {{ $stats['students_percentage'] >= 0 ? 'Higher' : 'Lower' }} than last month">
                                            {{ abs($stats['students_percentage']) }}% <i data-lucide="chevron-down" class="w-4 h-4 ml-0.5"></i>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-3xl font-medium leading-8 mt-6">{{ number_format($stats['total_students']) }}</div>
                                <div class="text-base text-slate-500 mt-1">Total Students</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        <div class="report-box zoom-in">
                            <div class="box p-5">
                                <div class="flex">
                                    <i data-lucide="calendar" class="report-box__icon text-pending"></i>
                                    <div class="ml-auto">
                                        @if($stats['events_percentage'] >= 0)
                                        <div class="report-box__indicator bg-success tooltip cursor-pointer" title="{{ abs($stats['events_percentage']) }}% {{ $stats['events_percentage'] >= 0 ? 'Higher' : 'Lower' }} than last month">
                                            {{ abs($stats['events_percentage']) }}% <i data-lucide="chevron-up" class="w-4 h-4 ml-0.5"></i>
                                        </div>
                                        @else
                                        <div class="report-box__indicator bg-danger tooltip cursor-pointer" title="{{ abs($stats['events_percentage']) }}% {{ $stats['events_percentage'] >= 0 ? 'Higher' : 'Lower' }} than last month">
                                            {{ abs($stats['events_percentage']) }}% <i data-lucide="chevron-down" class="w-4 h-4 ml-0.5"></i>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-3xl font-medium leading-8 mt-6">{{ number_format($stats['total_events']) }}</div>
                                <div class="text-base text-slate-500 mt-1">Total Events</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        <div class="report-box zoom-in">
                            <div class="box p-5">
                                <div class="flex">
                                    <i data-lucide="user" class="report-box__icon text-warning"></i>
                                    <div class="ml-auto">
                                        @if($stats['users_percentage'] >= 0)
                                        <div class="report-box__indicator bg-success tooltip cursor-pointer" title="{{ abs($stats['users_percentage']) }}% {{ $stats['users_percentage'] >= 0 ? 'Higher' : 'Lower' }} than last month">
                                            {{ abs($stats['users_percentage']) }}% <i data-lucide="chevron-up" class="w-4 h-4 ml-0.5"></i>
                                        </div>
                                        @else
                                        <div class="report-box__indicator bg-danger tooltip cursor-pointer" title="{{ abs($stats['users_percentage']) }}% {{ $stats['users_percentage'] >= 0 ? 'Higher' : 'Lower' }} than last month">
                                            {{ abs($stats['users_percentage']) }}% <i data-lucide="chevron-down" class="w-4 h-4 ml-0.5"></i>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-3xl font-medium leading-8 mt-6">{{ number_format($stats['total_users']) }}</div>
                                <div class="text-base text-slate-500 mt-1">Total Users</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        <div class="report-box zoom-in">
                            <div class="box p-5">
                                <div class="flex">
                                    <i data-lucide="check-square" class="report-box__icon text-success"></i>
                                    <div class="ml-auto">
                                        @if($stats['attendance_percentage'] >= 0)
                                        <div class="report-box__indicator bg-success tooltip cursor-pointer" title="{{ abs($stats['attendance_percentage']) }}% {{ $stats['attendance_percentage'] >= 0 ? 'Higher' : 'Lower' }} than last month">
                                            {{ abs($stats['attendance_percentage']) }}% <i data-lucide="chevron-up" class="w-4 h-4 ml-0.5"></i>
                                        </div>
                                        @else
                                        <div class="report-box__indicator bg-danger tooltip cursor-pointer" title="{{ abs($stats['attendance_percentage']) }}% {{ $stats['attendance_percentage'] >= 0 ? 'Higher' : 'Lower' }} than last month">
                                            {{ abs($stats['attendance_percentage']) }}% <i data-lucide="chevron-down" class="w-4 h-4 ml-0.5"></i>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-3xl font-medium leading-8 mt-6">{{ number_format($stats['total_attendance']) }}</div>
                                <div class="text-base text-slate-500 mt-1">Total Attendance</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END: General Report -->


            <!-- BEGIN: Additional Statistics -->
            <div class="col-span-12 mt-6">
                <div class="grid grid-cols-12 gap-6 mt-5">
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        <div class="report-box zoom-in">
                            <div class="box p-5">
                                <div class="flex">
                                    <i data-lucide="school" class="report-box__icon text-primary"></i>
                                </div>
                                <div class="text-3xl font-medium leading-8 mt-6">{{ number_format($stats['total_colleges']) }}</div>
                                <div class="text-base text-slate-500 mt-1">Total Colleges</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        <div class="report-box zoom-in">
                            <div class="box p-5">
                                <div class="flex">
                                    <i data-lucide="book-open" class="report-box__icon text-pending"></i>
                                </div>
                                <div class="text-3xl font-medium leading-8 mt-6">{{ number_format($stats['total_programs']) }}</div>
                                <div class="text-base text-slate-500 mt-1">Total Programs</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        <div class="report-box zoom-in">
                            <div class="box p-5">
                                <div class="flex">
                                    <i data-lucide="users" class="report-box__icon text-warning"></i>
                                </div>
                                <div class="text-3xl font-medium leading-8 mt-6">{{ number_format($stats['total_organizations']) }}</div>
                                <div class="text-base text-slate-500 mt-1">Total Organizations</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        <div class="report-box zoom-in">
                            <div class="box p-5">
                                <div class="flex">
                                    <i data-lucide="megaphone" class="report-box__icon text-success"></i>
                                </div>
                                <div class="text-3xl font-medium leading-8 mt-6">{{ number_format($stats['total_announcements']) }}</div>
                                <div class="text-base text-slate-500 mt-1">Total Announcements</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END: Additional Statistics -->
        </div>
    </div>
    <div class="col-span-12 2xl:col-span-3">
        <div class="2xl:border-l -mb-10 pb-10">
            <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                <!-- BEGIN: Transactions -->
                <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3 2xl:mt-8">
                    <div class="intro-x flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Attendance</h2>
                    </div>
                    <div class="mt-5">
                        @forelse($recentAttendances as $attendance)
                        <div class="intro-x">
                            <div class="box px-5 py-3 mb-3 flex items-center zoom-in">
                                <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                    <img alt="{{ $attendance['student_name'] }}" class="rounded-full" src="{{ $attendance['student_photo'] }}" onerror="this.src='{{ asset('dist/images/profile-5.jpg') }}'">
                                </div>
                                <div class="ml-4 mr-auto">
                                    <div class="font-medium">{{ $attendance['student_name'] }}</div>
                                    <div class="text-slate-500 text-xs mt-0.5">{{ $attendance['date_formatted'] }} • {{ $attendance['time_formatted'] }}</div>
                                    <div class="text-slate-400 text-xs mt-0.5">{{ $attendance['event_name'] }}</div>
                                </div>
                                <div class="{{ $attendance['is_time_in'] ? 'text-success' : 'text-warning' }} text-sm font-medium">
                                    {{ $attendance['workstate_text'] }}
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="intro-x">
                            <div class="box px-5 py-3 mb-3 text-center">
                                <div class="text-slate-500 text-sm">No attendance records found</div>
                            </div>
                        </div>
                        @endforelse
                        @if($recentAttendances->count() > 0)
                        <a href="{{ route('attendance.attendance') }}" class="intro-x w-full block text-center rounded-md py-3 border border-dotted border-slate-400 dark:border-darkmode-300 text-slate-500">View More</a>
                        @endif
                    </div>
                </div>
                <!-- END: Transactions -->
                <!-- BEGIN: Recent Activities -->
                <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                    <div class="intro-x flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Events</h2>
                        <a href="{{ route('events.add-event') }}" class="ml-auto text-primary truncate">Show More</a>
                    </div>
                    <div class="mt-5 relative before:block before:absolute before:w-px before:h-[85%] before:bg-slate-200 before:dark:bg-darkmode-400 before:ml-5 before:mt-5">
                        @forelse($recentEvents as $index => $event)
                            @php
                                $previousDate = $index > 0 ? $recentEvents[$index - 1]['date_formatted'] : null;
                                $currentDate = $event['date_formatted'];
                            @endphp
                            @if($index > 0 && $previousDate !== $currentDate)
                                <div class="intro-x text-slate-500 text-xs text-center my-4">{{ $currentDate }}</div>
                            @endif
                            <div class="intro-x relative flex items-center mb-3">
                                <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                                    <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden bg-primary/10 flex items-center justify-center">
                                        <i data-lucide="calendar" class="w-5 h-5 text-primary"></i>
                                    </div>
                                </div>
                                <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                                    <div class="flex items-center">
                                        <div class="font-medium">{{ $event['event_name'] }}</div>
                                        <div class="text-xs text-slate-500 ml-auto">{{ $event['time_formatted'] }}</div>
                                    </div>
                                    <div class="text-slate-500 mt-1">
                                        @if(!empty($event['event_description']))
                                            {{ Str::limit($event['event_description'], 50) }}
                                        @else
                                            New event created
                                        @endif
                                        @if($event['start_date'] !== '-')
                                            <span class="text-xs text-slate-400"> • Scheduled: {{ $event['start_date'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="intro-x relative flex items-center mb-3">
                                <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                                    <div class="text-slate-500 text-sm text-center">No recent events found</div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
                <!-- END: Recent Activities -->
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/dashboard/dashboard.js') }}?v={{ time() }}"></script>
@endpush