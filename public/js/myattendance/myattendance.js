// My Attendance JavaScript

// Function to toggle "Add to Cart" button visibility (defined in outer scope)
function toggleAddToCartButton() {
    const tableBody = document.getElementById('attendanceTableBody');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const selectAllCheckbox = document.getElementById('horizontal-form-3');
    
    if (!tableBody || !addToCartBtn) return;
    
    const checkedBoxes = tableBody.querySelectorAll('.attendance-row input[type="checkbox"]:checked');
    if (checkedBoxes.length > 0) {
        addToCartBtn.classList.remove('hidden');
        addToCartBtn.style.display = '';
    } else {
        addToCartBtn.classList.add('hidden');
        addToCartBtn.style.display = 'none';
    }
    
    // Update "Select All" checkbox state
    if (selectAllCheckbox) {
        const allCheckboxes = tableBody.querySelectorAll('.attendance-row input[type="checkbox"]');
        const checkedCount = tableBody.querySelectorAll('.attendance-row input[type="checkbox"]:checked').length;
        if (allCheckboxes.length > 0) {
            selectAllCheckbox.checked = (checkedCount === allCheckboxes.length);
            selectAllCheckbox.indeterminate = (checkedCount > 0 && checkedCount < allCheckboxes.length);
        }
    }
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('attendanceTableBody');
    const paginationContainer = document.getElementById('attendance-pagination');
    if (paginationContainer) {
        paginationContainer.classList.remove('hidden');
        paginationContainer.style.display = '';
    }

    // Update search placeholder
    if (searchInput) {
        searchInput.placeholder = 'Search event...';
    }
   
    if (searchInput && tableBody) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = tableBody.querySelectorAll('.attendance-row');
            
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
    
    // Handle "Select All" checkbox - only select rows with penalties
    const selectAllCheckbox = document.getElementById('horizontal-form-3');
    
    if (selectAllCheckbox && tableBody) {
        selectAllCheckbox.addEventListener('change', function() {
            const rows = tableBody.querySelectorAll('.attendance-row');
            rows.forEach(row => {
                // Only select rows that have checkboxes (rows with penalties)
                const checkbox = row.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = this.checked;
                }
            });
            toggleAddToCartButton();
        });
    }
    
    // Handle individual checkbox changes
    if (tableBody) {
        tableBody.addEventListener('change', function(e) {
            if (e.target.type === 'checkbox' && e.target.closest('.attendance-row')) {
                toggleAddToCartButton();
            }
        });
    }
    
    // Initial check for any pre-checked boxes
    toggleAddToCartButton();
    
    // Handle "Add to Cart" button click
    const addToCartBtn = document.getElementById('addToCartBtn');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            saveCartItems();
        });
    }
    
    // Add click event listeners to View Details buttons - use event delegation for dynamically loaded content
    document.addEventListener('click', function(e) {
        if (e.target.closest('.view-details-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.view-details-btn');
            const eventId = btn.getAttribute('data-event-id');
            console.log('View Details clicked:', { eventId });
            if (eventId) {
                viewAttendanceDetails(eventId);
            } else {
                console.error('Missing event_id');
            }
        }
        
        // Handle View Receipt button clicks
        if (e.target.closest('.view-receipt-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.view-receipt-btn');
            const paymentId = btn.getAttribute('data-payment-id');
            if (paymentId) {
                viewReceipt(paymentId);
            }
        }
    });
});

// Save cart items to server
function saveCartItems() {
    const tableBody = document.getElementById('attendanceTableBody');
    if (!tableBody) {
        console.error('Table body not found');
        return;
    }

    // Collect all checked items
    const checkedRows = tableBody.querySelectorAll('.attendance-row input[type="checkbox"]:checked');
    if (checkedRows.length === 0) {
        showMyAttendanceToast('No items selected', 'error');
        return;
    }

    const items = [];
    checkedRows.forEach(checkbox => {
        const row = checkbox.closest('.attendance-row');
        if (row) {
            const viewDetailsBtn = row.querySelector('.view-details-btn');
            const studentId = viewDetailsBtn?.getAttribute('data-student-id');
            const eventId = viewDetailsBtn?.getAttribute('data-event-id');
            
            if (studentId && eventId) {
                items.push({
                    student_id: studentId,
                    event_id: eventId
                });
            }
        }
    });

    if (items.length === 0) {
        showMyAttendanceToast('No valid items found', 'error');
        return;
    }

    // Disable button and show loading
    const addToCartBtn = document.getElementById('addToCartBtn');
    const originalText = addToCartBtn.innerHTML;
    addToCartBtn.disabled = true;
    addToCartBtn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2 inline-block"></div>Saving...';

    // Send to server
    fetch('/myattendance/cart/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ items: items })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMyAttendanceToast(data.message || 'Successfully added items to cart', 'success');
            
            // Uncheck all checkboxes
            const allCheckboxes = tableBody.querySelectorAll('.attendance-row input[type="checkbox"]');
            allCheckboxes.forEach(checkbox => checkbox.checked = false);
            
            // Hide "Select All" checkbox
            const selectAllCheckbox = document.getElementById('horizontal-form-3');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            }
            
            // Hide "Add to Cart" button
            toggleAddToCartButton();
            
            // Show errors if any
            if (data.errors && data.errors.length > 0) {
                console.warn('Errors occurred:', data.errors);
            }
        } else {
            showMyAttendanceToast(data.message || 'Failed to add items to cart', 'error');
            if (data.errors && data.errors.length > 0) {
                console.error('Errors:', data.errors);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMyAttendanceToast('An error occurred while saving cart items', 'error');
    })
    .finally(() => {
        // Re-enable button
        if (addToCartBtn) {
            addToCartBtn.disabled = false;
            addToCartBtn.innerHTML = originalText;
        }
    });
}

// Show toast notification - use global function directly
function showMyAttendanceToast(message, type = 'success') {
    // Check for global showToast function at call time
    if (typeof window !== 'undefined' && typeof window.showToast === 'function') {
        const title = type === 'success' ? 'Success!' : type === 'error' ? 'Error!' : 'Info!';
        // Call the global function directly
        window.showToast(type, title, message);
    } else {
        // Fallback: alert
        alert(message);
    }
}

// View attendance details for a specific event
function viewAttendanceDetails(eventId) {
    console.log('viewAttendanceDetails called:', { eventId });
    
    const modal = document.getElementById('attendanceDetailsModal');
    const modalContent = document.getElementById('attendanceModalContent');
    
    if (!modal) {
        console.error('Modal element not found!');
        return;
    }
    
    if (!modalContent) {
        console.error('Modal content element not found!');
        return;
    }
    
    // Show loading state immediately
    modalContent.innerHTML = `
        <div id="attendanceModalLoading" class="text-center py-8">
            <div class="flex justify-center mb-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
            <p class="text-slate-500">Loading attendance details...</p>
        </div>
    `;
    
    // Show modal using Tailwind UI
    const modalTrigger = document.createElement('div');
    modalTrigger.style.display = 'none';
    modalTrigger.setAttribute('data-tw-toggle', 'modal');
    modalTrigger.setAttribute('data-tw-target', '#attendanceDetailsModal');
    document.body.appendChild(modalTrigger);
    modalTrigger.click();
    setTimeout(() => document.body.removeChild(modalTrigger), 100);
    
    // Fetch attendance details for this event
    fetch(`/myattendance/event/${eventId}/details`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            displayEventAttendanceDetails(data);
        } else {
            modalContent.innerHTML = `
                <div class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-circle mx-auto mb-4 text-slate-300">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <p class="text-slate-500">${data.message || 'No attendance records found.'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading attendance details:', error);
        modalContent.innerHTML = `
            <div class="text-center py-8">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-circle mx-auto mb-4 text-red-300">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <p class="text-red-500">Failed to load attendance details. Please try again.</p>
            </div>
        `;
    });
}

// Display event attendance details in modal
function displayEventAttendanceDetails(data) {
    const modalContent = document.getElementById('attendanceModalContent');
    const student = data.student || {};
    const event = data.event || {};
    const records = data.attendance_records || [];
    const isAbsent = data.is_absent || false;
    const absenceFine = parseFloat(data.absence_fine || 0);
    const latePenalty = parseFloat(data.late_penalty || 0);
    const totalPenalty = parseFloat(data.total_penalty || 0);
    const lateDeduction = data.late_deduction || null;
    
    // Get photo URL
    const photoUrl = student.photo 
        ? `/storage/${student.photo.replace('storage/', '')}`
        : '/dist/images/preview-7.jpg';
    
    // Helper function to get allowed time based on record
    const getAllowedTime = (record) => {
        if (!lateDeduction) return '-';
        
        const workstateCode = record.workstate_code || '';
        const workstate = record.workstate || '';
        
        // For absent records, show the corresponding allowed time
        if (workstateCode === 'absent_morning' || workstate.includes('Morning')) {
            if (lateDeduction.morning) {
                const timeIn = lateDeduction.morning.time_in || '-';
                const timeOut = lateDeduction.morning.time_out || '-';
                return timeIn !== '-' && timeOut !== '-' ? `${timeIn} - ${timeOut}` : (timeIn !== '-' ? timeIn : (timeOut !== '-' ? timeOut : '-'));
            }
        } else if (workstateCode === 'absent_afternoon' || workstate.includes('Afternoon')) {
            if (lateDeduction.afternoon) {
                const timeIn = lateDeduction.afternoon.time_in || '-';
                const timeOut = lateDeduction.afternoon.time_out || '-';
                return timeIn !== '-' && timeOut !== '-' ? `${timeIn} - ${timeOut}` : (timeIn !== '-' ? timeIn : (timeOut !== '-' ? timeOut : '-'));
            }
        } else if (workstateCode === 'absent' || workstate.includes('Whole Day')) {
            // For whole day absent, show both morning and afternoon
            let result = '';
            if (lateDeduction.morning) {
                const timeIn = lateDeduction.morning.time_in || '-';
                const timeOut = lateDeduction.morning.time_out || '-';
                if (timeIn !== '-' || timeOut !== '-') {
                    result += `Morning: ${timeIn !== '-' && timeOut !== '-' ? `${timeIn} - ${timeOut}` : (timeIn !== '-' ? timeIn : timeOut)}`;
                }
            }
            if (lateDeduction.afternoon) {
                const timeIn = lateDeduction.afternoon.time_in || '-';
                const timeOut = lateDeduction.afternoon.time_out || '-';
                if (timeIn !== '-' || timeOut !== '-') {
                    if (result) result += '<br>';
                    result += `Afternoon: ${timeIn !== '-' && timeOut !== '-' ? `${timeIn} - ${timeOut}` : (timeIn !== '-' ? timeIn : timeOut)}`;
                }
            }
            return result || '-';
        } else {
            // For actual attendance records, determine which period based on event_schedule_type
            const recordTime = record.log_time ? new Date(record.log_time) : null;
            const scheduleType = lateDeduction.schedule_type || event.event_schedule_type || 'whole_day';
            
            if (!recordTime) return '-';
            
            // Get all period info first for comparison
            const morningStart = lateDeduction.morning && event.start_datetime_morning ? new Date(event.start_datetime_morning) : null;
            const morningEnd = lateDeduction.morning && event.end_datetime_morning ? new Date(event.end_datetime_morning) : null;
            const afternoonStart = lateDeduction.afternoon && event.start_datetime_afternoon ? new Date(event.start_datetime_afternoon) : null;
            const afternoonEnd = lateDeduction.afternoon && event.end_datetime_afternoon ? new Date(event.end_datetime_afternoon) : null;
            
            // Handle based on event_schedule_type
            if (scheduleType === 'half_day_morning') {
                // Half day morning - only show morning allowed times if log_time exceeds allowed time
                if (record.workstate === 'Time In' && lateDeduction.morning && lateDeduction.morning.time_in) {
                    const allowedTimeIn = new Date(morningStart.getFullYear(), morningStart.getMonth(), morningStart.getDate(), 
                        parseInt(lateDeduction.morning.time_in.split(':')[0]), 
                        parseInt(lateDeduction.morning.time_in.split(':')[1]), 
                        parseInt((lateDeduction.morning.time_in.split(':')[2] || '00')));
                    if (recordTime > allowedTimeIn) {
                        return lateDeduction.morning.time_in;
                    }
                    return '-';
                } else if (record.workstate === 'Time Out' && lateDeduction.morning && lateDeduction.morning.time_out) {
                    const allowedTimeOut = new Date(morningStart.getFullYear(), morningStart.getMonth(), morningStart.getDate(), 
                        parseInt(lateDeduction.morning.time_out.split(':')[0]), 
                        parseInt(lateDeduction.morning.time_out.split(':')[1]), 
                        parseInt((lateDeduction.morning.time_out.split(':')[2] || '00')));
                    if (recordTime > allowedTimeOut) {
                        return lateDeduction.morning.time_out;
                    }
                    return '-';
                }
            } else if (scheduleType === 'half_day_afternoon') {
                // Half day afternoon - only show afternoon allowed times if log_time exceeds allowed time
                if (record.workstate === 'Time In' && lateDeduction.afternoon && lateDeduction.afternoon.time_in) {
                    const allowedTimeIn = new Date(afternoonStart.getFullYear(), afternoonStart.getMonth(), afternoonStart.getDate(), 
                        parseInt(lateDeduction.afternoon.time_in.split(':')[0]), 
                        parseInt(lateDeduction.afternoon.time_in.split(':')[1]), 
                        parseInt((lateDeduction.afternoon.time_in.split(':')[2] || '00')));
                    if (recordTime > allowedTimeIn) {
                        return lateDeduction.afternoon.time_in;
                    }
                    return '-';
                } else if (record.workstate === 'Time Out' && lateDeduction.afternoon && lateDeduction.afternoon.time_out) {
                    const allowedTimeOut = new Date(afternoonStart.getFullYear(), afternoonStart.getMonth(), afternoonStart.getDate(), 
                        parseInt(lateDeduction.afternoon.time_out.split(':')[0]), 
                        parseInt(lateDeduction.afternoon.time_out.split(':')[1]), 
                        parseInt((lateDeduction.afternoon.time_out.split(':')[2] || '00')));
                    if (recordTime > allowedTimeOut) {
                        return lateDeduction.afternoon.time_out;
                    }
                    return '-';
                }
            } else if (scheduleType === 'whole_day') {
                // Whole day - determine based on time period
                
                // Check if it's morning record
                if (lateDeduction.morning && morningStart) {
                    if (record.workstate === 'Time In') {
                        // For Time In, check if it falls within morning period strictly
                        const isInMorning = recordTime >= morningStart && (!morningEnd || recordTime <= morningEnd);
                        if (isInMorning && lateDeduction.morning.time_in) {
                            // Check if log_time exceeds allowed time
                            const allowedTimeIn = new Date(morningStart.getFullYear(), morningStart.getMonth(), morningStart.getDate(), 
                                parseInt(lateDeduction.morning.time_in.split(':')[0]), 
                                parseInt(lateDeduction.morning.time_in.split(':')[1]), 
                                parseInt((lateDeduction.morning.time_in.split(':')[2] || '00')));
                            if (recordTime > allowedTimeIn) {
                                return lateDeduction.morning.time_in;
                            }
                            return '-';
                        }
                    } else if (record.workstate === 'Time Out') {
                        // For Time Out, check if it's on the same date as morning and before afternoon starts
                        const isInMorning = morningEnd && recordTime >= morningStart && recordTime <= morningEnd;
                        const isOnMorningDate = recordTime.getFullYear() === morningStart.getFullYear() &&
                            recordTime.getMonth() === morningStart.getMonth() &&
                            recordTime.getDate() === morningStart.getDate();
                        
                        // Check if time out is before afternoon starts
                        const isBeforeAfternoon = !afternoonStart || (afternoonStart && recordTime < afternoonStart);
                        
                        if ((isInMorning || (isOnMorningDate && isBeforeAfternoon)) && lateDeduction.morning.time_out) {
                            // Check if log_time exceeds allowed time
                            const allowedTimeOut = new Date(morningStart.getFullYear(), morningStart.getMonth(), morningStart.getDate(), 
                                parseInt(lateDeduction.morning.time_out.split(':')[0]), 
                                parseInt(lateDeduction.morning.time_out.split(':')[1]), 
                                parseInt((lateDeduction.morning.time_out.split(':')[2] || '00')));
                            if (recordTime > allowedTimeOut) {
                                return lateDeduction.morning.time_out;
                            }
                            return '-';
                        }
                    }
                }
                
                // Check if it's afternoon record
                if (lateDeduction.afternoon && afternoonStart) {
                    if (record.workstate === 'Time In') {
                        // For Time In, check if it falls within afternoon period strictly
                        const isInAfternoon = recordTime >= afternoonStart && (!afternoonEnd || recordTime <= afternoonEnd);
                        if (isInAfternoon && lateDeduction.afternoon.time_in) {
                            // Check if log_time exceeds allowed time
                            const allowedTimeIn = new Date(afternoonStart.getFullYear(), afternoonStart.getMonth(), afternoonStart.getDate(), 
                                parseInt(lateDeduction.afternoon.time_in.split(':')[0]), 
                                parseInt(lateDeduction.afternoon.time_in.split(':')[1]), 
                                parseInt((lateDeduction.afternoon.time_in.split(':')[2] || '00')));
                            if (recordTime > allowedTimeIn) {
                                return lateDeduction.afternoon.time_in;
                            }
                            return '-';
                        }
                    } else if (record.workstate === 'Time Out') {
                        // For Time Out, check if it's on the same date as afternoon and after morning ends
                        const isInAfternoon = afternoonEnd && recordTime >= afternoonStart && recordTime <= afternoonEnd;
                        const isOnAfternoonDate = recordTime.getFullYear() === afternoonStart.getFullYear() &&
                            recordTime.getMonth() === afternoonStart.getMonth() &&
                            recordTime.getDate() === afternoonStart.getDate();
                        
                        // Check if time out is after morning ends
                        const isAfterMorning = !morningEnd || (morningEnd && recordTime > morningEnd);
                        
                        if ((isInAfternoon || (isOnAfternoonDate && isAfterMorning)) && lateDeduction.afternoon.time_out) {
                            // Check if log_time exceeds allowed time
                            const allowedTimeOut = new Date(afternoonStart.getFullYear(), afternoonStart.getMonth(), afternoonStart.getDate(), 
                                parseInt(lateDeduction.afternoon.time_out.split(':')[0]), 
                                parseInt(lateDeduction.afternoon.time_out.split(':')[1]), 
                                parseInt((lateDeduction.afternoon.time_out.split(':')[2] || '00')));
                            if (recordTime > allowedTimeOut) {
                                return lateDeduction.afternoon.time_out;
                            }
                            return '-';
                        }
                    }
                }
            }
        }
        
        return '-';
    };
    
    // Build attendance records table
    let attendanceRows = '';
    if (records.length > 0) {
        records.forEach((record, index) => {
            let workstateBadge = '';
            let workstateText = record.workstate || '';
            
            // Determine badge color based on workstate
            if (record.workstate_code === 'absent_morning' || 
                record.workstate_code === 'absent_afternoon' || 
                record.workstate_code === 'absent' ||
                workstateText.includes('Absent')) {
                workstateBadge = 'bg-danger'; // Red for absent
            } else if (record.workstate === 'Time In') {
                workstateBadge = 'bg-success'; // Green for time in
            } else {
                workstateBadge = 'bg-warning'; // Yellow/Orange for time out
            }
            
            const allowedTime = getAllowedTime(record);
            
            attendanceRows += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td class="text-center">
                        <span class="inline-block px-2 py-1 rounded ${workstateBadge} text-white text-xs">
                            ${workstateText}
                        </span>
                    </td>
                    <td class="text-center">${record.log_time_formatted || record.log_time || '-'}</td>
                    <td class="text-center text-xs">${allowedTime}</td>
                </tr>
            `;
        });
    } else if (isAbsent) {
        attendanceRows = `
            <tr>
                <td colspan="4" class="text-center py-8">
                    <span class="text-danger font-medium">Absent - No attendance records</span>
                </td>
            </tr>
        `;
    } else {
        attendanceRows = `
            <tr>
                <td colspan="4" class="text-center py-8 text-slate-500">No attendance records found.</td>
            </tr>
        `;
    }
    
    modalContent.innerHTML = `
        <!-- Student & Event Info -->
        <div class="flex items-center mb-6 pb-6 border-b border-slate-200">
            <div class="w-16 h-16 rounded-full overflow-hidden mr-4">
                <img src="${photoUrl}" alt="${student.name}" class="w-full h-full object-cover" onerror="this.src='/dist/images/preview-7.jpg'">
            </div>
            <div class="flex-1">
                <h3 class="font-medium text-lg">${event.event_name || 'N/A'}</h3>
                <p class="text-slate-500 text-sm">${student.name || 'N/A'} (ID: ${student.id_number || 'N/A'})</p>
                <p class="text-slate-400 text-xs mt-1">Event Attendance Details</p>
            </div>
        </div>
        
        <!-- Status Summary -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="text-center p-3 bg-slate-50 rounded">
                <div class="text-xs text-slate-500 mb-1">Status</div>
                <div class="text-sm font-medium ${isAbsent ? 'text-danger' : 'text-success'}">
                    ${isAbsent ? 'Absent' : 'Present'}
                </div>
            </div>
            <div class="text-center p-3 bg-slate-50 rounded">
                <div class="text-xs text-slate-500 mb-1">Absence Fine</div>
                <div class="text-sm font-medium">₱${absenceFine.toFixed(2)}</div>
            </div>
            <div class="text-center p-3 bg-slate-50 rounded">
                <div class="text-xs text-slate-500 mb-1">Late Penalty</div>
                <div class="text-sm font-medium">₱${latePenalty.toFixed(2)}</div>
            </div>
            <div class="text-center p-3 bg-slate-50 rounded">
                <div class="text-xs text-slate-500 mb-1">Total Penalty</div>
                <div class="text-sm font-medium ${totalPenalty > 0 ? 'text-danger' : 'text-success'}">
                    ₱${totalPenalty.toFixed(2)}
                </div>
            </div>
        </div>
        
        <!-- Attendance Records -->
        <div class="mt-4">
            <h4 class="font-medium text-base mb-4">Attendance Records (${records.length})</h4>
            <div class="overflow-x-auto">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Type</th>
                            <th class="text-center">Date & Time</th>
                            <th class="text-center">Allowed Time<br><span class="text-xs text-slate-500">(Basis for Penalty)</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        ${attendanceRows}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

// Make function globally accessible
window.viewAttendanceDetails = viewAttendanceDetails;

// Load paginated my attendance table
function loadMyAttendance(page = 1, perPage = 10) {
    const tbody = document.getElementById('attendanceTableBody');
    const pagDiv = document.getElementById('attendance-pagination');
    if (!tbody) return;

    fetch(`/myattendance/list?page=${page}&per_page=${perPage}`, {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
    })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Failed to load');
        renderMyAttendanceRows(data.attendances || []);
        if (data.pagination && pagDiv) {
            renderMyPagination(data.pagination);
            pagDiv.classList.remove('hidden');
        }
    })
    .catch(err => {
        console.error(err);
        if (tbody) tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-slate-500">Failed to load records.</td></tr>';
        if (pagDiv) pagDiv.classList.add('hidden');
    });
}

function renderMyAttendanceRows(items) {
    const tbody = document.getElementById('attendanceTableBody');
    if (!tbody) return;
    if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center py-8 text-slate-500">No attendance records.</td></tr>';
        return;
    }
    const defaultPhoto = '/dist/images/preview-7.jpg';
    tbody.innerHTML = items.map(a => {
        const photo = a.student_photo ? `/${a.student_photo}` : defaultPhoto;
        const timeIn = a.time_in_formatted ? `<div class="text-xs">${a.time_in_formatted}</div>` : '<span class="text-slate-400">-</span>';
        const timeOut = a.time_out_formatted ? `<div class="text-xs">${a.time_out_formatted}</div>` : '<span class="text-slate-400">-</span>';
        
        // Check if has penalty and if payment is approved
        const hasPenalty = (a.absence_fine || 0) > 0 || (a.late_penalty || 0) > 0 || (a.total_penalty || 0) > 0;
        const paymentStatus = a.payment_status || null;
        const isApproved = paymentStatus === 'approved';
        const hasReceipt = a.has_receipt || false;
        const paymentId = a.payment_id || null;
        
        // Checkbox - only show if has penalty and not approved
        const checkboxHtml = hasPenalty && !isApproved 
            ? '<input class="form-check-input" type="checkbox" value="">' 
            : '';
        
        // View Receipt button - only show if approved and has receipt
        const viewReceiptHtml = isApproved && hasReceipt && paymentId
            ? `<a class="flex items-center text-success view-receipt-btn mr-3" href="javascript:;" data-payment-id="${paymentId}" title="View Receipt">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt w-4 h-4 mr-1">
                    <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1Z"></path>
                    <path d="M14 8H8"></path>
                    <path d="M16 12H8"></path>
                    <path d="M13 16H8"></path>
                </svg>
                View Receipt
            </a>`
            : '';
        
        return `
        <tr class="intro-x attendance-row" data-student-name="${(a.student_name||'').toLowerCase()}" data-student-id="${(a.student_id_number||'').toLowerCase()}" data-event-name="${(a.event_name||'').toLowerCase()}">
            <td class="w-10">${checkboxHtml}</td>
            <td class="w-40">
                <div class="flex items-center">
                    <div class="w-10 h-10 image-fit zoom-in">
                        <img alt="${a.student_name||''}" class="tooltip rounded-full" src="${photo}" onerror="this.src='${defaultPhoto}'">
                    </div>
                    <div class="ml-3">
                        <a href="javascript:;" class="font-medium whitespace-nowrap">${a.student_name||'N/A'}</a>
                        <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">ID: ${a.student_id_number||'N/A'}</div>
                    </div>
                </div>
            </td>
            <td><div class="font-medium whitespace-nowrap">${a.event_name||'N/A'}</div></td>
            <td class="text-center">${timeIn}</td>
            <td class="text-center">${timeOut}</td>
            <td class="text-center">
                ${a.status === 'Absent' ? '<span class="text-danger font-medium">Absent</span>' : '<span class="text-success font-medium">Present</span>'}
            </td>
            <td class="text-center">
                <span class="text-slate-600">₱${(a.absence_fine || 0).toFixed(2)}</span>
            </td>
            <td class="text-center">
                <span class="text-slate-600">₱${(a.late_penalty || 0).toFixed(2)}</span>
            </td>
            <td class="text-center">
                <span class="font-medium ${(a.total_penalty || 0) > 0 ? 'text-danger' : 'text-success'}">
                    ₱${(a.total_penalty || 0).toFixed(2)}
                </span>
            </td>
            <td class="table-report__action w-56">
                <div class="flex justify-center items-center">
                    <a class="flex items-center mr-3 view-details-btn" href="javascript:;" data-student-id="${a.student_id}" data-event-id="${a.event_id}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye w-4 h-4 mr-1"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        View Details
                    </a>
                    ${viewReceiptHtml}
                </div>
            </td>
        </tr>`;
    }).join('');
}

function renderMyPagination(pagination) {
    const paginationDiv = document.getElementById('attendance-pagination');
    if (!paginationDiv || !pagination) return;
    const currentPage = pagination.current_page;
    const lastPage = pagination.last_page;
    const pages = [];
    const showEllipsis = lastPage > 7;
    if (!showEllipsis) {
        for (let i=1;i<=lastPage;i++) pages.push(i);
    } else if (currentPage <= 3) {
        for (let i=1;i<=4;i++) pages.push(i);
        pages.push('...'); pages.push(lastPage);
    } else if (currentPage >= lastPage-2) {
        pages.push(1); pages.push('...');
        for (let i=lastPage-3;i<=lastPage;i++) pages.push(i);
    } else {
        pages.push(1); pages.push('...');
        pages.push(currentPage-1, currentPage, currentPage+1);
        pages.push('...'); pages.push(lastPage);
    }

    let html = `
    <div class="intro-y col-span-12 flex flex-wrap sm:flex-row sm:flex-nowrap items-center">
        <nav class="w-full sm:w-auto sm:mr-auto">
            <ul class="pagination">
                ${currentPage === 1 ? `
                <li class="page-item disabled"><span class="page-link"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-chevrons-left w-4 h-4\"><polyline points=\"11 17 6 12 11 7\"></polyline><polyline points=\"18 17 13 12 18 7\"></polyline></svg></span></li>` : `
                <li class="page-item"><a class="page-link" href="javascript:;" onclick="loadMyAttendance(1, ${pagination.per_page})"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-chevrons-left w-4 h-4\"><polyline points=\"11 17 6 12 11 7\"></polyline><polyline points=\"18 17 13 12 18 7\"></polyline></svg></a></li>`}
                ${currentPage === 1 ? `
                <li class="page-item disabled"><span class="page-link"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-chevron-left w-4 h-4\"><polyline points=\"15 18 9 12 15 6\"></polyline></svg></span></li>` : `
                <li class="page-item"><a class="page-link" href="javascript:;" onclick="loadMyAttendance(${currentPage-1}, ${pagination.per_page})"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-chevron-left w-4 h-4\"><polyline points=\"15 18 9 12 15 6\"></polyline></svg></a></li>`}
                ${pages.map(p => p==='...' ? `<li class=\"page-item disabled\"><span class=\"page-link\">...</span></li>` : (p===currentPage ? `<li class=\"page-item active\"><span class=\"page-link\">${p}</span></li>` : `<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:;\" onclick=\"loadMyAttendance(${p}, ${pagination.per_page})\">${p}</a></li>`)).join('')}
                ${currentPage < lastPage ? `
                <li class="page-item"><a class="page-link" href="javascript:;" onclick="loadMyAttendance(${currentPage+1}, ${pagination.per_page})"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-chevron-right w-4 h-4\"><polyline points=\"9 18 15 12 9 6\"></polyline></svg></a></li>` : `
                <li class="page-item disabled"><span class="page-link"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-chevron-right w-4 h-4\"><polyline points=\"9 18 15 12 9 6\"></polyline></svg></span></li>`}
                ${currentPage < lastPage ? `
                <li class="page-item"><a class="page-link" href="javascript:;" onclick="loadMyAttendance(${lastPage}, ${pagination.per_page})"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-chevrons-right w-4 h-4\"><polyline points=\"13 17 18 12 13 7\"></polyline><polyline points=\"6 17 11 12 6 7\"></polyline></svg></a></li>` : `
                <li class="page-item disabled"><span class="page-link"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-chevrons-right w-4 h-4\"><polyline points=\"13 17 18 12 13 7\"></polyline><polyline points=\"6 17 11 12 6 7\"></polyline></svg></span></li>`}
            </ul>
        </nav>
        <select class="w-20 form-select box mt-3 sm:mt-0" onchange="loadMyAttendance(1, this.value)">
            <option value="10" ${pagination.per_page == 10 ? 'selected' : ''}>10</option>
            <option value="25" ${pagination.per_page == 25 ? 'selected' : ''}>25</option>
            <option value="35" ${pagination.per_page == 35 ? 'selected' : ''}>35</option>
            <option value="50" ${pagination.per_page == 50 ? 'selected' : ''}>50</option>
        </select>
    </div>`;

    paginationDiv.innerHTML = html;
}

// Expose loader for pagination links
window.loadMyAttendance = loadMyAttendance;

// View receipt for a specific payment
function viewReceipt(paymentId) {
    fetch(`/myattendance/receipt/${paymentId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        return response.json().then(data => ({ status: response.status, data }));
    })
    .then(({ status, data }) => {
        if (data.success) {
            displayReceipt(data);
        } else {
            showMyAttendanceToast(data.message || 'Failed to load receipt', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMyAttendanceToast('An error occurred while loading receipt', 'error');
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
    } else {
        // Fallback: use data attributes
        const modalTrigger = document.createElement('div');
        modalTrigger.style.display = 'none';
        modalTrigger.setAttribute('data-tw-toggle', 'modal');
        modalTrigger.setAttribute('data-tw-target', '#receipt-modal');
        document.body.appendChild(modalTrigger);
        modalTrigger.click();
        setTimeout(() => document.body.removeChild(modalTrigger), 100);
    }

    // Handle print button
    const printBtn = document.getElementById('print-receipt-btn');
    if (printBtn) {
        printBtn.onclick = function() {
            window.print();
        };
    }
}

