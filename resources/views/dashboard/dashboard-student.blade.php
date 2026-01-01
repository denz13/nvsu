@extends('layouts.master')


@section('subcontent')
<div class="grid grid-cols-12 gap-6 col-span-12">
    <div class="col-span-12 2xl:col-span-9">
        <div class="grid grid-cols-12 gap-6">
            <!-- BEGIN: Student Profile Summary -->
            <div class="col-span-12 mt-8">
                <div class="intro-y box p-5">
                    <div class="flex items-center">
                        <div class="w-20 h-20 image-fit rounded-full overflow-hidden border-2 border-primary">
                            <img alt="{{ $student->student_name }}" src="{{ $student->student_photo ?? asset('dist/images/profile-5.jpg') }}" onerror="this.src='{{ asset('dist/images/profile-5.jpg') }}'">
                        </div>
                        <div class="ml-5">
                            <div class="text-2xl font-medium">Welcome, {{ $student->student_name }}!</div>
                            <div class="text-slate-500 mt-1">{{ $student->id_number }}</div>
                            <div class="text-slate-500 text-sm mt-0.5">
                                {{ $student->college ? $student->college->college_name : '' }} • 
                                {{ $student->program ? $student->program->program_name : '' }}
                            </div>
                        </div>
                        @if($activeSemester)
                        <div class="ml-auto text-right">
                            <div class="text-slate-500 text-xs">Current Semester</div>
                            <div class="text-lg font-medium">{{ $activeSemester->semester }}</div>
                            <div class="text-slate-500 text-sm">{{ $activeSemester->school_year }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- END: Student Profile Summary -->
            
            <!-- BEGIN: My Statistics -->
            <div class="col-span-12 mt-3">
                <div class="intro-y flex items-center h-10">
                    <h2 class="text-lg font-medium truncate mr-5">My Statistics</h2>
                </div>
                <div class="grid grid-cols-12 gap-6 mt-5">
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        <div class="report-box zoom-in">
                            <div class="box p-5">
                                <div class="flex">
                                    <i data-lucide="calendar" class="report-box__icon text-primary"></i>
                                </div>
                                <div class="text-3xl font-medium leading-8 mt-6">{{ number_format($stats['total_events']) }}</div>
                                <div class="text-base text-slate-500 mt-1">My Events</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        <div class="report-box zoom-in">
                            <div class="box p-5">
                                <div class="flex">
                                    <i data-lucide="check-square" class="report-box__icon text-success"></i>
                                </div>
                                <div class="text-3xl font-medium leading-8 mt-6">{{ number_format($stats['total_attendance']) }}</div>
                                <div class="text-base text-slate-500 mt-1">My Attendance</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        <div class="report-box zoom-in">
                            <div class="box p-5">
                                <div class="flex">
                                    <i data-lucide="alert-circle" class="report-box__icon text-danger"></i>
                                </div>
                                <div class="text-3xl font-medium leading-8 mt-6">₱{{ number_format($stats['unpaid_fines'], 2) }}</div>
                                <div class="text-base text-slate-500 mt-1">Unpaid Fines</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        <div class="report-box zoom-in">
                            <div class="box p-5">
                                <div class="flex">
                                    <i data-lucide="dollar-sign" class="report-box__icon text-warning"></i>
                                </div>
                                <div class="text-3xl font-medium leading-8 mt-6">₱{{ number_format($stats['total_fines'], 2) }}</div>
                                <div class="text-base text-slate-500 mt-1">Total Fines</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END: My Statistics -->

            <!-- BEGIN: Recent Payments -->
            <div class="col-span-12 mt-6">
                <div class="intro-y flex items-center h-10">
                    <h2 class="text-lg font-medium truncate mr-5">Recent Payments</h2>
                </div>
                <div class="intro-y box p-5 mt-5">
                    @if($recentPayments->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">Event</th>
                                    <th class="text-center whitespace-nowrap">Amount</th>
                                    <th class="text-center whitespace-nowrap">Status</th>
                                    <th class="text-center whitespace-nowrap">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $payment)
                                <tr>
                                    <td>{{ $payment['event_name'] }}</td>
                                    <td class="text-center">₱{{ number_format($payment['amount'], 2) }}</td>
                                    <td class="text-center">
                                        @php
                                            $status = $payment['status'] ?? 'pending';
                                            $statusBadge = 'bg-slate-500';
                                            $statusText = 'No Payment';
                                            
                                            if ($status === 'pending') {
                                                $statusBadge = 'bg-warning';
                                                $statusText = 'Pending';
                                            } elseif ($status === 'approved') {
                                                $statusBadge = 'bg-primary';
                                                $statusText = 'Approved';
                                            } elseif ($status === 'paid') {
                                                $statusBadge = 'bg-success';
                                                $statusText = 'Paid';
                                            } elseif ($status === 'declined') {
                                                $statusBadge = 'bg-danger';
                                                $statusText = 'Declined';
                                            }
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs {{ $statusBadge }} text-white">{{ $statusText }}</span>
                                    </td>
                                    <td class="text-center">{{ $payment['date_formatted'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-slate-500 py-5">
                        No payment records found
                    </div>
                    @endif
                </div>
            </div>
            <!-- END: Recent Payments -->
        </div>
    </div>
    <div class="col-span-12 2xl:col-span-3">
        <div class="2xl:border-l -mb-10 pb-10">
            <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                <!-- BEGIN: My Recent Attendance -->
                <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3 2xl:mt-8">
                    <div class="intro-x flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">My Recent Attendance</h2>
                    </div>
                    <div class="mt-5">
                        @forelse($recentAttendances as $attendance)
                        <div class="intro-x">
                            <div class="box px-5 py-3 mb-3 zoom-in">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-{{ $attendance['is_time_in'] ? 'success' : 'warning' }} rounded-full mr-3"></div>
                                    <div class="flex-1">
                                        <div class="font-medium">{{ $attendance['event_name'] }}</div>
                                        <div class="text-slate-500 text-xs mt-0.5">{{ $attendance['date_formatted'] }} • {{ $attendance['time_formatted'] }}</div>
                                    </div>
                                    <div class="{{ $attendance['is_time_in'] ? 'text-success' : 'text-warning' }} text-xs font-medium">
                                        {{ $attendance['workstate_text'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="intro-x">
                            <div class="box px-5 py-3 mb-3 text-center">
                                <div class="text-slate-500 text-sm">No attendance records yet</div>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
                <!-- END: My Recent Attendance -->
                
                <!-- BEGIN: Upcoming Events -->
                <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                    <div class="intro-x flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Upcoming Events</h2>
                    </div>
                    <div class="mt-5">
                        @forelse($upcomingEvents as $event)
                            <div class="intro-x">
                                <div class="box px-5 py-3 mb-3 zoom-in">
                                    <div class="flex items-start">
                                        <div class="w-10 h-10 flex-none bg-primary/10 rounded-full flex items-center justify-center mr-3">
                                            <i data-lucide="calendar" class="w-4 h-4 text-primary"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium">{{ $event['event_name'] }}</div>
                                            <div class="text-slate-500 text-xs mt-0.5">
                                                {{ $event['date_formatted'] }} • {{ $event['time_formatted'] }}
                                            </div>
                                            @if($event['event_description'])
                                            <div class="text-slate-400 text-xs mt-1">
                                                {{ Str::limit($event['event_description'], 40) }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="intro-x">
                                <div class="box px-5 py-3 mb-3 text-center">
                                    <div class="text-slate-500 text-sm">No upcoming events</div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
                <!-- END: Upcoming Events -->
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/dashboard/dashboard-student.js') }}?v={{ time() }}"></script>
@endpush