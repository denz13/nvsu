// Attendance Management JavaScript

let selectedEventId = 'all';

// Filter attendance by event
function filterByEvent(eventId) {
    selectedEventId = eventId;
    
    // Update UI - remove active class from all
    document.querySelectorAll('.event-item').forEach(item => {
        item.classList.remove('bg-primary', 'text-white');
        item.classList.add('hover:bg-slate-100');
    });
    
    // Add active class to selected
    if (eventId === 'all') {
        // All Events button
        const allEventsBtn = document.querySelector('a[onclick*="filterByEvent(\'all\')"]');
        if (allEventsBtn) {
            allEventsBtn.classList.add('bg-primary', 'text-white');
        }
        // Remove active from all event items
        document.querySelectorAll('.event-item').forEach(item => {
            item.classList.remove('bg-primary', 'text-white');
        });
    } else {
        // Specific event
        const selectedItem = document.querySelector(`[data-event-id="${eventId}"]`);
        if (selectedItem) {
            selectedItem.classList.add('bg-primary', 'text-white');
            selectedItem.classList.remove('hover:bg-slate-100');
        }
    }
    
    // Load attendance data for selected event
    loadAttendance(eventId);
}

// Load attendance data
function loadAttendance(eventId, page = 1, perPage = 10) {
    const attendanceList = document.getElementById('attendance-list');
    const loadingDiv = document.getElementById('attendance-loading');
    const emptyDiv = document.getElementById('attendance-empty');
    const initialDiv = document.getElementById('attendance-initial');
    const staticCards = attendanceList.querySelector('.static-template-cards');
    
    // Hide initial state and static template cards
    if (initialDiv) initialDiv.style.display = 'none';
    if (staticCards) staticCards.style.display = 'none';
    
    // Hide all static file cards (the template ones) - backup method
    attendanceList.querySelectorAll('.intro-y.col-span-6').forEach(card => {
        if (!card.classList.contains('attendance-card') && 
            card.id !== 'attendance-loading' && 
            card.id !== 'attendance-empty' &&
            card.id !== 'attendance-initial' &&
            !card.closest('.static-template-cards')) {
            card.style.display = 'none';
        }
    });
    
    // Show loading
    if (loadingDiv) {
        loadingDiv.classList.remove('hidden');
        loadingDiv.style.display = 'block';
    }
    if (emptyDiv) {
        emptyDiv.classList.add('hidden');
        emptyDiv.style.display = 'none';
    }
    
    // Clear existing attendance cards (but keep loading/empty divs)
    attendanceList.querySelectorAll('.attendance-card').forEach(card => {
        card.remove();
    });
    
    // Build URL with pagination
    let url = `/attendance/list?page=${page}&per_page=${perPage}`;
    if (eventId && eventId !== 'all') {
        url += `&event_id=${eventId}`;
    }
    
    // Fetch attendance data
    fetch(url, {
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
        // Hide loading - remove both class and inline style
        if (loadingDiv) {
            loadingDiv.classList.add('hidden');
            loadingDiv.style.display = 'none';
        }
        
        if (data.success && data.attendances && data.attendances.length > 0) {
            if (emptyDiv) {
                emptyDiv.classList.add('hidden');
                emptyDiv.style.display = 'none';
            }
            if (initialDiv) initialDiv.style.display = 'none';
            displayAttendanceCards(data.attendances);
            
            // Render pagination if data exists
            if (data.pagination) {
                renderPagination(data.pagination, eventId);
            }
        } else {
            if (emptyDiv) {
                emptyDiv.classList.remove('hidden');
                emptyDiv.style.display = 'block';
            }
            if (initialDiv) initialDiv.style.display = 'none';
            
            // Hide pagination if no data
            const paginationDiv = document.getElementById('attendance-pagination');
            if (paginationDiv) {
                paginationDiv.classList.add('hidden');
            }
        }
    })
    .catch(error => {
        console.error('Error loading attendance:', error);
        // Hide loading on error
        if (loadingDiv) {
            loadingDiv.classList.add('hidden');
            loadingDiv.style.display = 'none';
        }
        if (emptyDiv) {
            emptyDiv.classList.remove('hidden');
            emptyDiv.style.display = 'block';
        }
    });
}

// Display attendance cards
function displayAttendanceCards(attendances) {
    const attendanceList = document.getElementById('attendance-list');
    
    // Clear any existing attendance cards first
    attendanceList.querySelectorAll('.attendance-card').forEach(card => {
        card.remove();
    });
    
    // Add new attendance cards
    attendances.forEach(attendance => {
        const card = createAttendanceCard(attendance);
        attendanceList.appendChild(card);
    });
}

// Create attendance card
function createAttendanceCard(attendance) {
    const card = document.createElement('div');
    card.className = 'intro-y col-span-6 sm:col-span-4 md:col-span-3 2xl:col-span-2 attendance-card';
    
    // Get photo URL or default - photo path is stored as 'storage/students/filename.jpg'
    const photoUrl = attendance.student_photo 
        ? `/${attendance.student_photo}`
        : '/dist/images/preview-7.jpg';
    
    // Status indicator - red if absent, green if has time out, yellow if only time in
    let statusBadge, statusText;
    if (attendance.status === 'Absent') {
        statusBadge = 'bg-danger';
        statusText = 'Absent';
    } else if (attendance.time_out) {
        statusBadge = 'bg-success';
        statusText = 'Complete';
    } else {
        statusBadge = 'bg-warning';
        statusText = 'Incomplete';
    }
    
    // Build time display
    let timeDisplay = '';
    if (attendance.status === 'Absent') {
        timeDisplay = `
            <div class="text-slate-500 text-xs text-center mt-1">
                <span class="inline-block px-2 py-1 rounded bg-danger text-white text-xs mb-1">Absent</span>
            </div>
        `;
    } else if (attendance.time_in && attendance.time_out) {
        timeDisplay = `
            <div class="text-slate-500 text-xs text-center mt-1">
                <div class="text-slate-600 font-medium mb-0.5">Time In</div>
                <div class="text-slate-400 text-xxs">${attendance.time_in_formatted}</div>
            </div>
            <div class="text-slate-500 text-xs text-center mt-1">
                <div class="text-slate-600 font-medium mb-0.5">Time Out</div>
                <div class="text-slate-400 text-xxs">${attendance.time_out_formatted}</div>
            </div>
        `;
    } else if (attendance.time_in) {
        timeDisplay = `
            <div class="text-slate-500 text-xs text-center mt-1">
                <span class="inline-block px-2 py-1 rounded bg-warning text-white text-xs mb-1">Time In Only</span>
                <div class="text-slate-400 text-xxs mt-1">${attendance.time_in_formatted}</div>
            </div>
        `;
    } else if (attendance.time_out) {
        timeDisplay = `
            <div class="text-slate-500 text-xs text-center mt-1">
                <span class="inline-block px-2 py-1 rounded bg-success text-white text-xs mb-1">Time Out Only</span>
                <div class="text-slate-400 text-xxs mt-1">${attendance.time_out_formatted}</div>
            </div>
        `;
    }
    
    // Build penalties display
    const absenceFine = attendance.absence_fine || 0;
    const latePenalty = attendance.late_penalty || 0;
    const totalPenalty = attendance.total_penalty || 0;
    let penaltiesDisplay = '';
    if (totalPenalty > 0 || attendance.status === 'Absent') {
        penaltiesDisplay = `
            <div class="mt-2 pt-2 border-t border-slate-200">
                <div class="text-xs text-slate-600 text-center">
                    ${absenceFine > 0 ? `<div>Absence: <span class="font-medium">₱${absenceFine.toFixed(2)}</span></div>` : ''}
                    ${latePenalty > 0 ? `<div>Late: <span class="font-medium">₱${latePenalty.toFixed(2)}</span></div>` : ''}
                    <div class="mt-1 ${totalPenalty > 0 ? 'text-danger font-bold' : 'text-success'}">
                        Total: ₱${totalPenalty.toFixed(2)}
                    </div>
                </div>
            </div>
        `;
    }
    
    card.innerHTML = `
        <div class="file box rounded-md px-5 pt-8 pb-5 px-3 sm:px-5 relative zoom-in">
            <div class="absolute left-0 top-0 mt-3 ml-3">
                <span class="w-3 h-3 ${statusBadge} rounded-full inline-block"></span>
            </div>
            <a href="javascript:;" class="w-3/5 file__icon file__icon--image mx-auto">
                <div class="file__icon--image__preview image-fit">
                    <img alt="${attendance.student_name}" src="${photoUrl}" onerror="this.src='/dist/images/preview-7.jpg'">
                </div>
            </a>
            <a href="javascript:;" class="block font-medium mt-4 text-center truncate">${attendance.student_name}</a> 
            <div class="text-slate-500 text-xs text-center mt-0.5">ID: ${attendance.student_id_number}</div>
            ${timeDisplay}
            ${penaltiesDisplay}
            <div class="absolute top-0 right-0 mr-2 mt-3 dropdown ml-auto">
                <a class="dropdown-toggle w-5 h-5 block" href="javascript:;" aria-expanded="false" data-tw-toggle="dropdown">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-more-vertical w-5 h-5 text-slate-500">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="12" cy="5" r="1"></circle>
                        <circle cx="12" cy="19" r="1"></circle>
                    </svg>
                </a>
                <div class="dropdown-menu w-40">
                    <ul class="dropdown-content">
                        <li>
                            <a href="javascript:;" class="dropdown-item" onclick="viewAttendanceDetails(${attendance.student_id}, ${attendance.event_id})">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye w-4 h-4 mr-2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                View Details
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    `;
    
    return card;
}

// Render pagination using the same structure as pagination.blade.php
function renderPagination(pagination, eventId) {
    const paginationDiv = document.getElementById('attendance-pagination');
    if (!paginationDiv || pagination.total === 0) {
        if (paginationDiv) paginationDiv.classList.add('hidden');
        return;
    }
    
    const currentPage = pagination.current_page;
    const lastPage = pagination.last_page;
    const baseUrl = pagination.base_url;
    const eventParam = pagination.event_param || '';
    
    // Generate page numbers
    const pages = [];
    const showEllipsis = lastPage > 7; // Show ellipsis if more than 7 pages
    
    if (!showEllipsis) {
        // Show all pages
        for (let i = 1; i <= lastPage; i++) {
            pages.push(i);
        }
    } else {
        // Show pages with ellipsis
        if (currentPage <= 3) {
            // Show first pages
            for (let i = 1; i <= 4; i++) {
                pages.push(i);
            }
            pages.push('ellipsis');
            pages.push(lastPage);
        } else if (currentPage >= lastPage - 2) {
            // Show last pages
            pages.push(1);
            pages.push('ellipsis');
            for (let i = lastPage - 3; i <= lastPage; i++) {
                pages.push(i);
            }
        } else {
            // Show middle pages
            pages.push(1);
            pages.push('ellipsis');
            for (let i = currentPage - 1; i <= currentPage + 1; i++) {
                pages.push(i);
            }
            pages.push('ellipsis');
            pages.push(lastPage);
        }
    }
    
    // Build pagination HTML
    let paginationHTML = `
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-row sm:flex-nowrap items-center">
            <nav class="w-full sm:w-auto sm:mr-auto">
                <ul class="pagination">
                    <!-- First Page -->
                    ${pagination.on_first_page 
                        ? `<li class="page-item disabled">
                            <span class="page-link"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevrons-left w-4 h-4"><polyline points="11 17 6 12 11 7"></polyline><polyline points="18 17 13 12 18 7"></polyline></svg> </span>
                        </li>`
                        : `<li class="page-item">
                            <a class="page-link" href="javascript:;" onclick="loadAttendance('${eventId || 'all'}', 1, ${pagination.per_page})"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevrons-left w-4 h-4"><polyline points="11 17 6 12 11 7"></polyline><polyline points="18 17 13 12 18 7"></polyline></svg> </a>
                        </li>`
                    }
                    
                    <!-- Previous Page -->
                    ${pagination.on_first_page
                        ? `<li class="page-item disabled">
                            <span class="page-link"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left w-4 h-4"><polyline points="15 18 9 12 15 6"></polyline></svg> </span>
                        </li>`
                        : `<li class="page-item">
                            <a class="page-link" href="javascript:;" onclick="loadAttendance('${eventId || 'all'}', ${currentPage - 1}, ${pagination.per_page})"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left w-4 h-4"><polyline points="15 18 9 12 15 6"></polyline></svg> </a>
                        </li>`
                    }
                    
                    <!-- Page Numbers -->
                    ${pages.map(page => {
                        if (page === 'ellipsis') {
                            return `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                        } else if (page === currentPage) {
                            return `<li class="page-item active"><span class="page-link">${page}</span></li>`;
                        } else {
                            return `<li class="page-item"><a class="page-link" href="javascript:;" onclick="loadAttendance('${eventId || 'all'}', ${page}, ${pagination.per_page})">${page}</a></li>`;
                        }
                    }).join('')}
                    
                    <!-- Next Page -->
                    ${pagination.has_more
                        ? `<li class="page-item">
                            <a class="page-link" href="javascript:;" onclick="loadAttendance('${eventId || 'all'}', ${currentPage + 1}, ${pagination.per_page})"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right w-4 h-4"><polyline points="9 18 15 12 9 6"></polyline></svg> </a>
                        </li>`
                        : `<li class="page-item disabled">
                            <span class="page-link"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right w-4 h-4"><polyline points="9 18 15 12 9 6"></polyline></svg> </span>
                        </li>`
                    }
                    
                    <!-- Last Page -->
                    ${pagination.has_more
                        ? `<li class="page-item">
                            <a class="page-link" href="javascript:;" onclick="loadAttendance('${eventId || 'all'}', ${lastPage}, ${pagination.per_page})"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevrons-right w-4 h-4"><polyline points="13 17 18 12 13 7"></polyline><polyline points="6 17 11 12 6 7"></polyline></svg> </a>
                        </li>`
                        : `<li class="page-item disabled">
                            <span class="page-link"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevrons-right w-4 h-4"><polyline points="13 17 18 12 13 7"></polyline><polyline points="6 17 11 12 6 7"></polyline></svg> </span>
                        </li>`
                    }
                </ul>
            </nav>
            <select class="w-20 form-select box mt-3 sm:mt-0" onchange="loadAttendance('${eventId || 'all'}', 1, this.value)">
                <option value="10" ${pagination.per_page == 10 ? 'selected' : ''}>10</option>
                <option value="25" ${pagination.per_page == 25 ? 'selected' : ''}>25</option>
                <option value="35" ${pagination.per_page == 35 ? 'selected' : ''}>35</option>
                <option value="50" ${pagination.per_page == 50 ? 'selected' : ''}>50</option>
            </select>
        </div>
    `;
    
    paginationDiv.innerHTML = paginationHTML;
    paginationDiv.classList.remove('hidden');
}

// View attendance details for a specific student and event
function viewAttendanceDetails(studentId, eventId) {
    const modal = document.getElementById('attendanceDetailsModal');
    const modalContent = document.getElementById('attendanceModalContent');
    
    // Show loading state immediately
    modalContent.innerHTML = `
        <div id="attendanceModalLoading" class="text-center py-8">
            <div class="flex justify-center mb-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
            <p class="text-slate-500">Loading attendance details...</p>
        </div>
    `;
    
    // Show modal using common.js function or Tailwind UI
    if (typeof modal_show !== 'undefined') {
        modal_show('attendanceDetailsModal');
    } else if (modal && typeof tailwind !== 'undefined' && tailwind.Modal) {
        const modalInstance = tailwind.Modal.getOrCreateInstance(modal);
        modalInstance.show();
    } else if (modal) {
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
    }
    
    // Fetch attendance details
    fetch(`/attendance/student?student_id=${studentId}&event_id=${eventId}`, {
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
        if (data.success && data.attendances) {
            displayAttendanceDetails(data);
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

// Display attendance details in modal
function displayAttendanceDetails(data) {
    const modalContent = document.getElementById('attendanceModalContent');
    const student = data.student || {};
    const event = data.event || {};
    const attendances = data.attendances || [];
    
    // Get photo URL
    const photoUrl = student.photo 
        ? `/${student.photo}`
        : '/dist/images/preview-7.jpg';
    
    let attendanceRows = '';
    if (attendances.length > 0) {
        attendances.forEach((attendance, index) => {
            const workstateBadge = attendance.workstate == "0" || attendance.workstate == 0
                ? 'bg-success'
                : 'bg-warning';
            
            attendanceRows += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td class="text-center">
                        <span class="inline-block px-2 py-1 rounded ${workstateBadge} text-white text-xs">
                            ${attendance.workstate_text}
                        </span>
                    </td>
                    <td class="text-center">${attendance.log_time_formatted || attendance.log_time}</td>
                    <td class="text-center">${attendance.scan_by}</td>
                </tr>
            `;
        });
    } else {
        attendanceRows = `
            <tr>
                <td colspan="4" class="text-center py-8 text-slate-500">No attendance records found.</td>
            </tr>
        `;
    }
    
    modalContent.innerHTML = `
        <!-- Student Info -->
        <div class="flex items-center mb-6 pb-6 border-b border-slate-200">
            <div class="w-20 h-20 rounded-full overflow-hidden mr-4">
                <img src="${photoUrl}" alt="${student.name}" class="w-full h-full object-cover" onerror="this.src='/dist/images/preview-7.jpg'">
            </div>
            <div>
                <h3 class="font-medium text-lg">${student.name || 'N/A'}</h3>
                <p class="text-slate-500 text-sm">ID: ${student.id_number || 'N/A'}</p>
                <p class="text-slate-400 text-xs mt-1">${event.name || 'N/A'}</p>
            </div>
        </div>
        
        <!-- Attendance Records -->
        <div class="mt-4">
            <h4 class="font-medium text-base mb-4">Attendance Records (${attendances.length})</h4>
            <div class="overflow-x-auto">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Type</th>
                            <th class="text-center">Date & Time</th>
                            <th class="text-center">Scanned By</th>
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

// Make functions globally accessible
window.filterByEvent = filterByEvent;
window.loadAttendance = loadAttendance;
window.viewAttendanceDetails = viewAttendanceDetails;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize - don't load on page load, let user select first
    const initialDiv = document.getElementById('attendance-initial');
    if (initialDiv) {
        initialDiv.style.display = 'block';
    }
    
    // Add click event listeners to event items
    document.querySelectorAll('.event-item').forEach(item => {
        item.addEventListener('click', function() {
            const eventId = this.getAttribute('data-event-id');
            if (eventId) {
                filterByEvent(eventId);
            }
        });
    });
    
    // Add click event listener to "All Events" button
    const allEventsBtn = document.querySelector('a[onclick*="filterByEvent(\'all\')"]');
    if (allEventsBtn) {
        allEventsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            filterByEvent('all');
        });
    }
});

