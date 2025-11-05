@extends('layouts.master')

@section('subcontent')
    <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-12">
        <h2 class="text-lg font-medium mr-auto">My Attendance</h2>
        <button id="addToCartBtn" class="btn btn-primary mr-3 hidden" style="display: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart w-4 h-4 mr-2">
                <circle cx="8" cy="21" r="1"></circle>
                <circle cx="19" cy="21" r="1"></circle>
                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
            </svg>
            Add to Cart
        </button>
        <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
            <div class="w-56 relative text-slate-500">
                <input type="text" id="searchInput" class="form-control w-56 box pr-10" placeholder="Search student...">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg> 
            </div>
        </div>
    </div>
    <!-- BEGIN: Data List -->
    <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
        <table class="table table-report -mt-2">
            <thead>
                <tr>
                    <th class="w-10"><input id="horizontal-form-3" class="form-check-input" type="checkbox" value=""></th>
                    <th class="whitespace-nowrap">STUDENT</th>
                    <th class="whitespace-nowrap">EVENT</th>
                    <th class="text-center whitespace-nowrap">TIME IN</th>
                    <th class="text-center whitespace-nowrap">TIME OUT</th>
                    <th class="text-center whitespace-nowrap">STATUS</th>
                    <th class="text-center whitespace-nowrap">ABSENCE FINE</th>
                    <th class="text-center whitespace-nowrap">LATE PENALTY</th>
                    <th class="text-center whitespace-nowrap">TOTAL PENALTY</th>
                    <th class="text-center whitespace-nowrap">ACTIONS</th>
                </tr>
            </thead>
            <tbody id="attendanceTableBody">
                @forelse($formattedAttendances as $attendance)
                    @php
                        $photoUrl = $attendance['student_photo'] 
                            ? asset('storage/' . str_replace('storage/', '', $attendance['student_photo']))
                            : asset('dist/images/preview-7.jpg');
                        $statusBadge = $attendance['time_out'] 
                            ? 'bg-success' 
                            : 'bg-warning';
                        $statusText = $attendance['time_out'] 
                            ? 'Complete' 
                            : 'Incomplete';
                    @endphp
                    <tr class="intro-x attendance-row" 
                        data-student-name="{{ strtolower($attendance['student_name']) }}"
                        data-student-id="{{ strtolower($attendance['student_id_number']) }}"
                        data-event-name="{{ strtolower($attendance['event_name']) }}">
                        <td class="w-10">
                            @php
                                $hasPenalty = (isset($attendance['absence_fine']) && $attendance['absence_fine'] > 0) ||
                                              (isset($attendance['late_penalty']) && $attendance['late_penalty'] > 0) ||
                                              (isset($attendance['total_penalty']) && $attendance['total_penalty'] > 0);
                                $paymentStatus = $attendance['payment_status'] ?? null;
                                $isApproved = $paymentStatus === 'approved';
                            @endphp
                            @if($hasPenalty && !$isApproved)
                                <input class="form-check-input" type="checkbox" value="">
                            @endif
                        </td>
                        <td class="w-40">
                            <div class="flex items-center">
                                <div class="w-10 h-10 image-fit zoom-in">
                                    @php
                                        $defaultPhoto = asset('dist/images/preview-7.jpg');
                                    @endphp
                                    <img alt="{{ $attendance['student_name'] }}" class="tooltip rounded-full" src="{{ $photoUrl }}" onerror="this.src='{{ $defaultPhoto }}'">
                                </div>
                                <div class="ml-3">
                                    <a href="javascript:;" class="font-medium whitespace-nowrap">{{ $attendance['student_name'] }}</a> 
                                    <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">ID: {{ $attendance['student_id_number'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="font-medium whitespace-nowrap">{{ $attendance['event_name'] }}</div>
                        </td>
                        <td class="text-center">
                            @if($attendance['time_in_formatted'])
                                <div class="text-xs">{{ $attendance['time_in_formatted'] }}</div>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($attendance['time_out_formatted'])
                                <div class="text-xs">{{ $attendance['time_out_formatted'] }}</div>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(isset($attendance['status']) && $attendance['status'] === 'Absent')
                                <span class="text-danger font-medium">Absent</span>
                            @else
                                <span class="text-success font-medium">Present</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="text-slate-600">₱{{ number_format($attendance['absence_fine'] ?? 0, 2) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-slate-600">₱{{ number_format($attendance['late_penalty'] ?? 0, 2) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="font-medium {{ ($attendance['total_penalty'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                ₱{{ number_format($attendance['total_penalty'] ?? 0, 2) }}
                            </span>
                        </td>
                        <td class="table-report__action w-56">
                            <div class="flex justify-center items-center">
                                <a class="flex items-center mr-3 view-details-btn" 
                                   href="javascript:;" 
                                   data-student-id="{{ $attendance['student_id'] }}"
                                   data-event-id="{{ $attendance['event_id'] }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye w-4 h-4 mr-1">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg> 
                                    View Details
                                </a>
                                @php
                                    $paymentStatus = $attendance['payment_status'] ?? null;
                                    $hasReceipt = $attendance['has_receipt'] ?? false;
                                    $paymentId = $attendance['payment_id'] ?? null;
                                @endphp
                                @if($paymentStatus === 'approved' && $hasReceipt && $paymentId)
                                <a class="flex items-center text-success view-receipt-btn" 
                                   href="javascript:;" 
                                   data-payment-id="{{ $paymentId }}"
                                   title="View Receipt">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt w-4 h-4 mr-1">
                                        <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1Z"></path>
                                        <path d="M14 8H8"></path>
                                        <path d="M16 12H8"></path>
                                        <path d="M13 16H8"></path>
                                    </svg>
                                    View Receipt
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-12">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-check mx-auto mb-4 text-slate-300">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                                <polyline points="17 11 19 13 23 9"></polyline>
                            </svg>
                            <h5 class="text-lg font-medium mb-2">No Attendance Records</h5>
                            <p class="text-slate-500">No students have attendance records yet.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- END: Data List -->
    <!-- BEGIN: Pagination (dynamic like attendance page) -->
    <div id="attendance-pagination" class="hidden mt-6">
        <!-- Pagination will be rendered here dynamically -->
    </div>
    <!-- END: Pagination -->
</div>

<!-- BEGIN: Attendance Details Modal -->
<div id="attendanceDetailsModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">Attendance Details</h2>
                <a href="javascript:;" data-tw-dismiss="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x w-8 h-8 text-slate-400" data-lucide="x">
                        <path d="M18 6L6 18"></path>
                        <path d="M6 6l12 12"></path>
                    </svg>
                </a>
            </div>
            <div class="modal-body p-6">
                <div id="attendanceModalContent">
                    <!-- Loading state -->
                    <div id="attendanceModalLoading" class="text-center py-8">
                        <div class="flex justify-center mb-4">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                        </div>
                        <p class="text-slate-500">Loading attendance details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="btn btn-secondary w-20 mr-1">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- END: Attendance Details Modal -->

<!-- BEGIN: Receipt Modal -->
<div id="receipt-modal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">Official Receipt</h2>
                <a href="javascript:;" data-tw-dismiss="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x w-8 h-8 text-slate-400" data-lucide="x">
                        <path d="M18 6L6 18"></path>
                        <path d="M6 6l12 12"></path>
                    </svg>
                </a>
            </div>
            <div class="modal-body p-0">
                <div id="receipt-content">
                    <!-- Receipt content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-tw-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="print-receipt-btn">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END: Receipt Modal -->
</div>

<!-- BEGIN: Toast Component -->
@include('components.toast')
<!-- END: Toast Component -->

@endsection

@push('scripts')
<script src="{{ asset('js/myattendance/myattendance.js') }}?v={{ time() }}"></script>
@endpush
