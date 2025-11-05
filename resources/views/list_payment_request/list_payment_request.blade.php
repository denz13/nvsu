@extends('layouts.master')

@section('subcontent')

<div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-12">
    <h2 class="text-lg font-medium mr-auto">List Payment Request</h2>
    <div class="hidden md:block mx-auto text-slate-500">Showing {{ $paymentRequests->firstItem() ?? 0 }} to {{ $paymentRequests->lastItem() ?? 0 }} of {{ $paymentRequests->total() }} entries</div>
    <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
        <div class="w-56 relative text-slate-500">
            <input type="text" id="searchPaymentInput" class="form-control w-56 box pr-10" placeholder="Search...">
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
                <th class="whitespace-nowrap">STUDENT</th>
                <th class="whitespace-nowrap">EVENT</th>
                <th class="text-center whitespace-nowrap">SCHEDULE PERIODS</th>
                <th class="text-center whitespace-nowrap">AMOUNT PAID</th>
                <th class="text-center whitespace-nowrap">PAYMENT STATUS</th>
                <th class="text-center whitespace-nowrap">REQUEST DATE</th>
                <th class="text-center whitespace-nowrap">ACTIONS</th>
            </tr>
        </thead>
        <tbody id="paymentRequestTableBody">
            @forelse($formattedPayments as $payment)
            @php
                $photoUrl = $payment['student_photo'] 
                    ? asset('storage/' . str_replace('storage/', '', $payment['student_photo']))
                    : asset('dist/images/preview-7.jpg');
                $paymentStatusBadge = $payment['payment_status'] === 'pending' 
                    ? 'bg-warning' 
                    : ($payment['payment_status'] === 'approved' 
                        ? 'bg-primary' 
                        : ($payment['payment_status'] === 'paid' 
                            ? 'bg-success' 
                            : ($payment['payment_status'] === 'declined' 
                                ? 'bg-danger' 
                                : 'bg-warning')));
                $paymentStatusText = ucfirst($payment['payment_status'] ?? 'pending');
            @endphp
            <tr class="intro-x payment-request-row" 
                data-student-name="{{ strtolower($payment['student_name']) }}"
                data-student-id="{{ strtolower($payment['student_id_number']) }}"
                data-event-name="{{ strtolower($payment['event_name']) }}">
                <td class="w-40">
                    <div class="flex items-center">
                        <div class="w-10 h-10 image-fit zoom-in">
                            @php
                                $defaultPhoto = asset('dist/images/preview-7.jpg');
                            @endphp
                            <img alt="{{ $payment['student_name'] }}" class="tooltip rounded-full" src="{{ $photoUrl }}" onerror="this.src='{{ $defaultPhoto }}'">
                        </div>
                        <div class="ml-3">
                            <a href="javascript:;" class="font-medium whitespace-nowrap">{{ $payment['student_name'] }}</a>
                            <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">ID: {{ $payment['student_id_number'] }}</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">{{ $payment['college'] }} / {{ $payment['program'] }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="font-medium whitespace-nowrap">{{ $payment['event_name'] }}</div>
                </td>
                <td class="text-center">
                    <span class="text-xs">{{ $payment['schedule_periods'] }}</span>
                    <div class="text-slate-500 text-xs mt-1">
                        Time In: {{ $payment['time_in_count'] }} | Time Out: {{ $payment['time_out_count'] }}
                    </div>
                </td>
                <td class="text-center">
                    <span class="font-medium text-slate-600">₱{{ number_format($payment['amount_paid'], 2) }}</span>
                </td>
                <td class="text-center">
                    <span class="px-2 py-1 text-xs rounded-full {{ $paymentStatusBadge }} text-white">
                        {{ $paymentStatusText }}
                    </span>
                </td>
                <td class="text-center">
                    <div class="text-xs">{{ $payment['created_at'] ? $payment['created_at']->format('M d, Y h:i A') : '-' }}</div>
                </td>
                <td class="table-report__action w-56">
                    <div class="flex justify-center items-center gap-2">
                        <a class="flex items-center view-payment-details-btn" 
                           href="javascript:;" 
                           data-payment-id="{{ $payment['id'] }}"
                           title="View Details">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye w-4 h-4">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </a>
                        @if($payment['payment_status'] === 'pending')
                        <a class="flex items-center text-success approve-payment-btn" 
                           href="javascript:;" 
                           data-payment-id="{{ $payment['id'] }}"
                           title="Approve">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle w-4 h-4">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </a>
                        <a class="flex items-center text-danger decline-payment-btn" 
                           href="javascript:;" 
                           data-payment-id="{{ $payment['id'] }}"
                           title="Decline">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-circle w-4 h-4">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M15 9l-6 6"></path>
                                <path d="M9 9l6 6"></path>
                            </svg>
                        </a>
                        @endif
                        <a class="flex items-center text-primary add-waiver-btn" 
                           href="javascript:;" 
                           data-payment-id="{{ $payment['id'] }}"
                           title="Add Waiver">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text w-4 h-4">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </a>
                        @if($payment['payment_status'] === 'approved')
                        <a class="flex items-center text-success generate-receipt-btn" 
                           href="javascript:;" 
                           data-payment-id="{{ $payment['id'] }}"
                           title="Generate Receipt">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt w-4 h-4">
                                <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1Z"></path>
                                <path d="M14 8H8"></path>
                                <path d="M16 12H8"></path>
                                <path d="M13 16H8"></path>
                            </svg>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-x mx-auto mb-4 text-slate-300">
                        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="9" y1="15" x2="15" y2="9"></line>
                        <line x1="15" y1="15" x2="9" y2="9"></line>
                    </svg>
                    <h5 class="text-lg font-medium mb-2">No Payment Requests</h5>
                    <p class="text-slate-500">No students have requested payment yet.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<!-- END: HTML Table Data -->
<!-- BEGIN: Pagination -->
{{ $paymentRequests->links('components.pagination') }}
<!-- END: Pagination -->

<!-- BEGIN: Toast Component -->
@include('components.toast')
<!-- END: Toast Component -->

<!-- BEGIN: Payment Details Modal -->
<div id="paymentDetailsModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">Payment Request Details</h2>
                <a href="javascript:;" data-tw-dismiss="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x w-8 h-8 text-slate-400" data-lucide="x">
                        <path d="M18 6L6 18"></path>
                        <path d="M6 6l12 12"></path>
                    </svg>
                </a>
            </div>
            <div class="modal-body p-6">
                <div id="paymentModalContent">
                    <!-- Loading state -->
                    <div id="paymentModalLoading" class="text-center py-8">
                        <div class="flex justify-center mb-4">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                        </div>
                        <p class="text-slate-500">Loading payment details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="btn btn-secondary w-20 mr-1">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- END: Payment Details Modal -->

<!-- BEGIN: Add Waiver Modal -->
@include('components.modal', [
'modalId' => 'add-waiver-modal',
'size' => 'md',
'title' => 'Add Waiver',
'body' => '
<form id="add-waiver-form">
    <input type="hidden" id="waiver-payment-id" name="payment_id" />
    <div class="grid grid-cols-1 gap-4">
        <div>
            <label class="form-label">Waiver Reason <span class="text-danger">*</span></label>
            <textarea class="form-control" id="waiver-reason" name="waiver_reason" rows="4" placeholder="Enter waiver reason" required></textarea>
        </div>
        <div>
            <label class="form-label">Waiver Amount</label>
            <input type="number" class="form-control" id="waiver-amount" name="waiver_amount" placeholder="0.00" step="0.01" min="0">
            <div class="text-xs text-slate-500 mt-1">Leave empty to waive full amount</div>
        </div>
    </div>
</form>
',
'footer' => '
<button type="button" class="btn btn-secondary" data-tw-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="save-waiver-btn">
    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Waiver
</button>
',
'showButton' => false
])
<!-- END: Add Waiver Modal -->

<!-- BEGIN: Confirmation Modal -->
@include('components.modal', [
'modalId' => 'confirmation-modal',
'size' => 'sm',
'title' => 'Confirmation',
'body' => '
<div class="p-5 text-center">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle w-16 h-16 text-warning mx-auto mt-3">
        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
        <path d="M12 9v4"></path>
        <path d="M12 17h.01"></path>
    </svg>
    <div class="text-slate-500 mt-4" id="confirmation-message">Are you sure you want to proceed?</div>
</div>
',
'footer' => '
<button type="button" class="btn btn-secondary mr-2" data-tw-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary" id="confirm-action-btn">
    <i data-lucide="check" class="w-4 h-4 mr-2"></i> Confirm
</button>
',
'showButton' => false
])
<!-- END: Confirmation Modal -->

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

@endsection
@push('scripts')
<script src="{{ asset('js/list_payment_request/list_payment_request.js') }}?v={{ time() }}"></script>
@endpush


