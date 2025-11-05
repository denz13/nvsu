// List Payment Request JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchPaymentInput');
    const tableBody = document.getElementById('paymentRequestTableBody');

    // Search functionality
    if (searchInput && tableBody) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = tableBody.querySelectorAll('.payment-request-row');
            
            rows.forEach(row => {
                const studentName = row.getAttribute('data-student-name') || '';
                const studentId = row.getAttribute('data-student-id') || '';
                const eventName = row.getAttribute('data-event-name') || '';
                
                const matches = 
                    studentName.includes(searchTerm) ||
                    studentId.includes(searchTerm) ||
                    eventName.includes(searchTerm);
                
                if (matches || searchTerm === '') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Handle View Payment Details button clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.view-payment-details-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.view-payment-details-btn');
            const paymentId = btn.getAttribute('data-payment-id');
            if (paymentId) {
                viewPaymentDetails(paymentId);
            }
        }
        
        // Handle Approve Payment button clicks
        if (e.target.closest('.approve-payment-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.approve-payment-btn');
            const paymentId = btn.getAttribute('data-payment-id');
            if (paymentId) {
                approvePayment(paymentId);
            }
        }
        
        // Handle Decline Payment button clicks
        if (e.target.closest('.decline-payment-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.decline-payment-btn');
            const paymentId = btn.getAttribute('data-payment-id');
            if (paymentId) {
                declinePayment(paymentId);
            }
        }
        
        // Handle Add Waiver button clicks
        if (e.target.closest('.add-waiver-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.add-waiver-btn');
            const paymentId = btn.getAttribute('data-payment-id');
            if (paymentId) {
                openAddWaiverModal(paymentId);
            }
        }
        
        // Handle Generate Receipt button clicks
        if (e.target.closest('.generate-receipt-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.generate-receipt-btn');
            const paymentId = btn.getAttribute('data-payment-id');
            if (paymentId) {
                generateReceipt(paymentId);
            }
        }
    });
    
    // Handle Save Waiver button click
    const saveWaiverBtn = document.getElementById('save-waiver-btn');
    if (saveWaiverBtn) {
        saveWaiverBtn.addEventListener('click', function(e) {
            e.preventDefault();
            saveWaiver();
        });
    }
});

// View payment details for a specific payment request
function viewPaymentDetails(paymentId) {
    const modal = document.getElementById('paymentDetailsModal');
    const modalContent = document.getElementById('paymentModalContent');

    if (!modal || !modalContent) {
        console.error('Modal elements not found!');
        return;
    }

    // Show loading state
    modalContent.innerHTML = `
        <div id="paymentModalLoading" class="text-center py-8">
            <div class="flex justify-center mb-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
            <p class="text-slate-500">Loading payment details...</p>
        </div>
    `;

    // Show modal - try multiple methods for compatibility
    if (typeof tailwind !== 'undefined' && tailwind.Modal) {
        const modalInstance = tailwind.Modal.getOrCreateInstance(modal);
        modalInstance.show();
    } else {
        // Fallback: use data attributes
        modal.setAttribute('data-tw-toggle', 'modal');
        modal.setAttribute('data-tw-target', '#paymentDetailsModal');
        const event = new Event('click');
        modal.dispatchEvent(event);
    }

    // Fetch payment details
    fetch(`/listpaymentrequest/${paymentId}/details`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayPaymentDetails(data);
        } else {
            modalContent.innerHTML = `
                <div class="text-center py-8">
                    <p class="text-danger">${data.message || 'Failed to load payment details'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalContent.innerHTML = `
            <div class="text-center py-8">
                <p class="text-danger">An error occurred while loading payment details</p>
            </div>
        `;
    });
}

// Display payment details in modal
function displayPaymentDetails(data) {
    const modalContent = document.getElementById('paymentModalContent');
    
    const payment = data.payment || {};
    const student = data.student || {};
    const event = data.event || {};
    const timeSchedules = data.time_schedules || [];

    // Format time schedules into a table
    let timeScheduleRows = '';
    if (timeSchedules.length > 0) {
        timeSchedules.forEach((schedule, index) => {
            const logTime = schedule.log_time ? new Date(schedule.log_time).toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            }) : '-';
            const workstateText = schedule.workstate == 0 ? 'Time In' : 'Time Out';
            const workstateBadge = schedule.workstate == 0 
                ? '<span class="px-2 py-1 text-xs rounded-full bg-success text-white">Time In</span>'
                : '<span class="px-2 py-1 text-xs rounded-full bg-warning text-white">Time Out</span>';
            
            timeScheduleRows += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td class="text-center">${workstateBadge}</td>
                    <td class="text-center">${logTime}</td>
                    <td class="text-center">${schedule.type_of_schedule_pay ? schedule.type_of_schedule_pay.replace('_', ' ').toUpperCase() : '-'}</td>
                </tr>
            `;
        });
    } else {
        timeScheduleRows = `
            <tr>
                <td colspan="4" class="text-center text-slate-500 py-4">No time schedules found</td>
            </tr>
        `;
    }

    modalContent.innerHTML = `
        <div class="grid grid-cols-2 gap-x-6 gap-y-4 mb-6">
            <div>
                <div class="text-xs text-slate-500 mb-1">Student Name</div>
                <div class="font-medium text-sm">${student.student_name || '-'}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500 mb-1">Student ID</div>
                <div class="font-medium text-sm">${student.id_number || '-'}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500 mb-1">College</div>
                <div class="font-medium text-sm">${student.college || '-'}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500 mb-1">Program</div>
                <div class="font-medium text-sm">${student.program || '-'}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500 mb-1">Event Name</div>
                <div class="font-medium text-sm">${event.event_name || '-'}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500 mb-1">Amount Paid</div>
                <div class="font-medium text-sm text-slate-600">
                    ₱${parseFloat(payment.amount_paid || 0).toFixed(2)}
                    ${payment.waiver_amount > 0 ? `<div class="text-xs text-slate-400 mt-0.5">Waived from ₱${parseFloat(payment.original_amount || payment.amount_paid || 0).toFixed(2)}</div>` : ''}
                </div>
            </div>
            ${payment.waiver_amount > 0 ? `
            <div>
                <div class="text-xs text-slate-500 mb-1">Waiver Amount</div>
                <div class="font-medium text-sm text-danger">₱${parseFloat(payment.waiver_amount || 0).toFixed(2)}</div>
            </div>
            ` : ''}
            <div>
                <div class="text-xs text-slate-500 mb-1">Payment Status</div>
                <div class="font-medium">
                    <span class="px-2 py-1 text-xs rounded-full ${
                        payment.payment_status === 'pending' 
                            ? 'bg-warning' 
                            : payment.payment_status === 'approved'
                                ? 'bg-primary'
                                : payment.payment_status === 'paid' 
                                    ? 'bg-success' 
                                    : payment.payment_status === 'declined'
                                        ? 'bg-danger'
                                        : 'bg-warning'
                    } text-white">
                        ${payment.payment_status ? payment.payment_status.toUpperCase() : 'PENDING'}
                    </span>
                </div>
            </div>
            ${payment.waiver_amount > 0 ? `
            <div class="col-span-2">
                <div class="text-xs text-slate-500 mb-1">Waiver Reason</div>
                <div class="font-medium text-sm">${payment.waiver_reason || '-'}</div>
            </div>
            ` : ''}
            <div>
                <div class="text-xs text-slate-500 mb-1">Request Date</div>
                <div class="font-medium text-sm">${payment.created_at || '-'}</div>
            </div>
        </div>
        <div class="mt-6">
            <div class="text-xs text-slate-500 mb-3">Time Schedules</div>
            <div class="overflow-x-auto">
                <table class="table table-report">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Type</th>
                            <th class="text-center">Date & Time</th>
                            <th class="text-center">Schedule Period</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${timeScheduleRows}
                    </tbody>
                </table>
            </div>
        </div>
        ${payment.waiver_amount > 0 ? `
        <div class="mt-6">
            <div class="text-xs text-slate-500 mb-3">Waiver Statement</div>
            <div class="grid grid-cols-2 gap-4 p-3 rounded-md bg-slate-50 border border-slate-200">
                <div class="flex items-start">
                    <div class="w-32 whitespace-nowrap">
                        <div class="text-xs text-slate-500">Status:</div>
                    </div>
                    <div class="font-medium flex-1 text-sm">
                        ${parseFloat(payment.amount_paid || 0) === 0 ? 'Fully waived' : 'Partially waived'}
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-32 whitespace-nowrap">
                        <div class="text-xs text-slate-500">Waiver Amount:</div>
                    </div>
                    <div class="font-medium flex-1 text-sm text-danger">
                        ₱${parseFloat(payment.waiver_amount || 0).toFixed(2)}
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-32 whitespace-nowrap">
                        <div class="text-xs text-slate-500">From:</div>
                    </div>
                    <div class="font-medium flex-1 text-sm">
                        ₱${parseFloat(payment.original_amount || 0).toFixed(2)}
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-32 whitespace-nowrap">
                        <div class="text-xs text-slate-500">To:</div>
                    </div>
                    <div class="font-medium flex-1 text-sm">
                        ₱${parseFloat(payment.amount_paid || 0).toFixed(2)}
                    </div>
                </div>
                <div class="flex items-start col-span-2">
                    <div class="w-32 whitespace-nowrap">
                        <div class="text-xs text-slate-500">Reason:</div>
                    </div>
                    <div class="font-medium flex-1 text-sm break-words">
                        ${payment.waiver_reason || '-'}
                    </div>
                </div>
            </div>
        </div>
        ` : ''}
    `;
}

// Approve payment
function approvePayment(paymentId) {
    showConfirmationModal(
        'Are you sure you want to approve this payment request?',
        () => {
            fetch(`/listpaymentrequest/${paymentId}/approve`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showListPaymentRequestToast('Payment approved successfully', 'success');
            // Reload page to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showListPaymentRequestToast(data.message || 'Failed to approve payment', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showListPaymentRequestToast('An error occurred while approving payment', 'error');
    });
        }
    );
}

// Decline payment
function declinePayment(paymentId) {
    showConfirmationModal(
        'Are you sure you want to decline this payment request?',
        () => {
            fetch(`/listpaymentrequest/${paymentId}/decline`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showListPaymentRequestToast('Payment declined successfully', 'success');
            // Reload page to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showListPaymentRequestToast(data.message || 'Failed to decline payment', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showListPaymentRequestToast('An error occurred while declining payment', 'error');
    });
        }
    );
}

// Show confirmation modal
function showConfirmationModal(message, onConfirm) {
    const modal = document.getElementById('confirmation-modal');
    const messageDiv = document.getElementById('confirmation-message');
    const confirmBtn = document.getElementById('confirm-action-btn');
    
    if (!modal || !messageDiv || !confirmBtn) {
        console.error('Confirmation modal elements not found!');
        // Fallback to browser confirm
        if (confirm(message)) {
            onConfirm();
        }
        return;
    }
    
    // Set message
    messageDiv.textContent = message;
    
    // Remove previous event listeners by cloning the button
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    // Add new event listener
    newConfirmBtn.addEventListener('click', function() {
        // Close modal
        if (typeof tailwind !== 'undefined' && tailwind.Modal) {
            const modalInstance = tailwind.Modal.getOrCreateInstance(modal);
            modalInstance.hide();
        }
        // Execute confirmation callback
        onConfirm();
    });
    
    // Show modal
    if (typeof tailwind !== 'undefined' && tailwind.Modal) {
        const modalInstance = tailwind.Modal.getOrCreateInstance(modal);
        modalInstance.show();
    }
}

// Open Add Waiver modal
function openAddWaiverModal(paymentId) {
    const modal = document.getElementById('add-waiver-modal');
    const paymentIdInput = document.getElementById('waiver-payment-id');
    const reasonTextarea = document.getElementById('waiver-reason');
    const amountInput = document.getElementById('waiver-amount');
    
    if (!modal || !paymentIdInput) {
        console.error('Waiver modal elements not found!');
        return;
    }
    
    // Set payment ID
    paymentIdInput.value = paymentId;
    
    // Clear form
    if (reasonTextarea) reasonTextarea.value = '';
    if (amountInput) amountInput.value = '';
    
    // Show modal
    if (typeof tailwind !== 'undefined' && tailwind.Modal) {
        const modalInstance = tailwind.Modal.getOrCreateInstance(modal);
        modalInstance.show();
    }
}

// Save waiver
function saveWaiver() {
    const form = document.getElementById('add-waiver-form');
    const paymentIdInput = document.getElementById('waiver-payment-id');
    const reasonTextarea = document.getElementById('waiver-reason');
    const amountInput = document.getElementById('waiver-amount');
    const saveBtn = document.getElementById('save-waiver-btn');
    
    if (!form || !paymentIdInput || !reasonTextarea) {
        console.error('Form elements not found!');
        return;
    }
    
    const paymentId = paymentIdInput.value;
    const waiverReason = reasonTextarea.value.trim();
    const waiverAmount = amountInput.value ? parseFloat(amountInput.value) : null;
    
    // Validate
    if (!waiverReason) {
        showListPaymentRequestToast('Please enter a waiver reason', 'error');
        reasonTextarea.focus();
        return;
    }
    
    if (waiverAmount !== null && waiverAmount < 0) {
        showListPaymentRequestToast('Waiver amount cannot be negative', 'error');
        amountInput.focus();
        return;
    }
    
    // Disable button during request
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>';
    }
    
    // Prepare form data
    const formData = new FormData();
    formData.append('waiver_reason', waiverReason);
    if (waiverAmount !== null) {
        formData.append('waiver_amount', waiverAmount);
    }
    
    fetch(`/listpaymentrequest/${paymentId}/waiver`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showListPaymentRequestToast('Waiver added successfully', 'success');
            
            // Close modal
            const modal = document.getElementById('add-waiver-modal');
            if (modal && typeof tailwind !== 'undefined' && tailwind.Modal) {
                const modalInstance = tailwind.Modal.getOrCreateInstance(modal);
                modalInstance.hide();
            }
            
            // Reload page to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showListPaymentRequestToast(data.message || 'Failed to add waiver', 'error');
            if (data.errors) {
                console.error('Validation errors:', data.errors);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showListPaymentRequestToast('An error occurred while adding waiver', 'error');
    })
    .finally(() => {
        // Re-enable button
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Waiver';
        }
    });
}

// Generate receipt
function generateReceipt(paymentId) {
    fetch(`/listpaymentrequest/${paymentId}/generate-receipt`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        return response.json().then(data => ({ status: response.status, data }));
    })
    .then(({ status, data }) => {
        if (data.success) {
            // Display receipt directly without confirmation (newly generated or existing)
            displayReceipt(data);
        } else {
            showListPaymentRequestToast(data.message || 'Failed to generate receipt', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showListPaymentRequestToast('An error occurred while generating receipt', 'error');
    });
}

// Display receipt
function displayReceipt(data) {
    const modal = document.getElementById('receipt-modal');
    const receiptContent = document.getElementById('receipt-content');
    
    if (!modal || !receiptContent) {
        console.error('Receipt modal elements not found!');
        return;
    }

    const receipt = data.receipt || {};
    const payment = data.payment || {};
    const student = data.student || {};
    const event = data.event || {};
    const timeSchedules = data.time_schedules || [];

    // Group time schedules by type_of_schedule_pay for receipt items
    const scheduleGroups = {};
    timeSchedules.forEach(schedule => {
        const key = schedule.type_of_schedule_pay || 'unknown';
        if (!scheduleGroups[key]) {
            scheduleGroups[key] = {
                type: key,
                count: 0,
                description: key.replace('_', ' ').toUpperCase()
            };
        }
        scheduleGroups[key].count++;
    });

    // Calculate totals (ensure they are numbers)
    const originalAmount = parseFloat(payment.original_amount || payment.amount_paid || 0);
    const waiverAmount = parseFloat(payment.waiver_amount || 0);
    const finalAmount = parseFloat(payment.amount_paid || 0);

    // Build receipt items HTML
    let receiptItemsHtml = '';
    const items = Object.values(scheduleGroups);
    items.forEach((item, index) => {
        const isLast = index === items.length - 1;
        const borderClass = isLast ? '' : 'border-b dark:border-darkmode-400';
        const pricePerItem = timeSchedules.length > 0 ? parseFloat((originalAmount / timeSchedules.length).toFixed(2)) : originalAmount;
        const subtotal = parseFloat((pricePerItem * item.count).toFixed(2));
        
        receiptItemsHtml += `
            <tr>
                <td class="${borderClass}">
                    <div class="font-medium whitespace-nowrap">${event.event_name || 'Event Attendance'}</div>
                    <div class="text-slate-500 text-sm mt-0.5 whitespace-nowrap">${item.description}</div>
                </td>
                <td class="text-right ${borderClass} w-32">${item.count}</td>
                <td class="text-right ${borderClass} w-32">₱${parseFloat(pricePerItem).toFixed(2)}</td>
                <td class="text-right ${borderClass} w-32 font-medium">₱${parseFloat(subtotal).toFixed(2)}</td>
            </tr>
        `;
    });

    // If no items, show at least one row
    if (items.length === 0) {
        receiptItemsHtml = `
            <tr>
                <td>
                    <div class="font-medium whitespace-nowrap">${event.event_name || 'Event Attendance'}</div>
                    <div class="text-slate-500 text-sm mt-0.5 whitespace-nowrap">Payment for attendance</div>
                </td>
                <td class="text-right w-32">1</td>
                <td class="text-right w-32">₱${parseFloat(originalAmount).toFixed(2)}</td>
                <td class="text-right w-32 font-medium">₱${parseFloat(originalAmount).toFixed(2)}</td>
            </tr>
        `;
    }

    // Build receipt HTML
    receiptContent.innerHTML = `
        <div class="intro-y box overflow-hidden mt-5">
            <div class="border-b border-slate-200/60 dark:border-darkmode-400 text-center sm:text-left">
                <div class="px-5 py-10 sm:px-20 sm:py-20">
                    <div class="text-primary font-semibold text-3xl">OFFICIAL RECEIPT</div>
                    <div class="mt-2">Receipt <span class="font-medium">#${receipt.official_receipts || 'N/A'}</span></div>
                    <div class="mt-1">${receipt.created_at || new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</div>
                </div>
                <div class="flex flex-col lg:flex-row px-5 sm:px-20 pt-10 pb-10 sm:pb-20">
                    <div>
                        <div class="text-base text-slate-500">Student Details</div>
                        <div class="text-lg font-medium text-primary mt-2">${student.student_name || 'N/A'}</div>
                        <div class="mt-1">ID Number: ${student.id_number || 'N/A'}</div>
                        <div class="mt-1">${student.college || 'N/A'} / ${student.program || 'N/A'}</div>
                    </div>
                    <div class="lg:text-right mt-10 lg:mt-0 lg:ml-auto">
                        <div class="text-base text-slate-500">Event</div>
                        <div class="text-lg font-medium text-primary mt-2">${event.event_name || 'N/A'}</div>
                    </div>
                </div>
            </div>
            <div class="px-5 sm:px-16 py-10 sm:py-20">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="border-b-2 dark:border-darkmode-400 whitespace-nowrap">DESCRIPTION</th>
                                <th class="border-b-2 dark:border-darkmode-400 text-right whitespace-nowrap">QTY</th>
                                <th class="border-b-2 dark:border-darkmode-400 text-right whitespace-nowrap">PRICE</th>
                                <th class="border-b-2 dark:border-darkmode-400 text-right whitespace-nowrap">SUBTOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${receiptItemsHtml}
                        </tbody>
                    </table>
                </div>
            </div>
            ${waiverAmount > 0 ? `
            <div class="px-5 sm:px-20 pb-5">
                <div class="text-right">
                    <div class="text-base text-slate-500">Subtotal</div>
                    <div class="text-lg font-medium mt-1">₱${parseFloat(originalAmount).toFixed(2)}</div>
                    <div class="text-base text-slate-500 mt-2">Waiver</div>
                    <div class="text-lg font-medium text-danger mt-1">-₱${parseFloat(waiverAmount).toFixed(2)}</div>
                    ${payment.waiver_reason ? `<div class="text-xs text-slate-400 mt-1">${payment.waiver_reason}</div>` : ''}
                </div>
            </div>
            ` : ''}
            <div class="px-5 sm:px-20 pb-10 sm:pb-20">
                <div class="text-center sm:text-right">
                    <div class="text-base text-slate-500">Total Amount</div>
                    <div class="text-xl text-primary font-medium mt-2">₱${parseFloat(finalAmount).toFixed(2)}</div>
                    ${waiverAmount > 0 ? `<div class="mt-1 text-xs text-slate-400">After waiver</div>` : ''}
                </div>
            </div>
        </div>
    `;

    // Show modal
    if (typeof tailwind !== 'undefined' && tailwind.Modal) {
        const modalInstance = tailwind.Modal.getOrCreateInstance(modal);
        modalInstance.show();
    }

    // Handle print button
    const printBtn = document.getElementById('print-receipt-btn');
    if (printBtn) {
        printBtn.onclick = function() {
            window.print();
        };
    }

    // Reload page after closing modal to reflect changes (using Tailwind modal)
    // Listen for modal close event
    const observer = new MutationObserver(function(mutations) {
        if (!modal.classList.contains('show') && modal.style.display === 'none') {
            setTimeout(() => {
                window.location.reload();
            }, 500);
            observer.disconnect();
        }
    });
    
    // Observe modal for changes
    if (modal) {
        observer.observe(modal, {
            attributes: true,
            attributeFilter: ['class', 'style']
        });
    }
}

// Toast notification helper
function showListPaymentRequestToast(message, type = 'success') {
    if (typeof window.showToast === 'function') {
        window.showToast(message, type === 'success' ? 'success' : 'error');
    } else {
        alert(message);
    }
}

