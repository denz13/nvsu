// Events Management JavaScript

// Function to toggle datetime fields based on schedule type
function toggleDatetimeFields(scheduleType, prefix) {
    const morningStart = document.getElementById(`${prefix}-morning-start-group`);
    const morningEnd = document.getElementById(`${prefix}-morning-end-group`);
    const afternoonStart = document.getElementById(`${prefix}-afternoon-start-group`);
    const afternoonEnd = document.getElementById(`${prefix}-afternoon-end-group`);
    
    const morningStartInput = document.getElementById(`${prefix}-start-datetime-morning`);
    const morningEndInput = document.getElementById(`${prefix}-end-datetime-morning`);
    const afternoonStartInput = document.getElementById(`${prefix}-start-datetime-afternoon`);
    const afternoonEndInput = document.getElementById(`${prefix}-end-datetime-afternoon`);
    
    // Hide all fields first
    [morningStart, morningEnd, afternoonStart, afternoonEnd].forEach(el => {
        if (el) el.style.display = 'none';
    });
    
    // Remove required attribute from all inputs
    [morningStartInput, morningEndInput, afternoonStartInput, afternoonEndInput].forEach(el => {
        if (el) el.removeAttribute('required');
    });
    
    // Show fields based on schedule type
    if (scheduleType === 'whole_day') {
        [morningStart, morningEnd, afternoonStart, afternoonEnd].forEach(el => {
            if (el) el.style.display = 'block';
        });
        [morningStartInput, morningEndInput, afternoonStartInput, afternoonEndInput].forEach(el => {
            if (el) el.setAttribute('required', 'required');
        });
    } else if (scheduleType === 'half_day_morning') {
        if (morningStart) morningStart.style.display = 'block';
        if (morningEnd) morningEnd.style.display = 'block';
        if (morningStartInput) morningStartInput.setAttribute('required', 'required');
        if (morningEndInput) morningEndInput.setAttribute('required', 'required');
    } else if (scheduleType === 'half_day_afternoon') {
        if (afternoonStart) afternoonStart.style.display = 'block';
        if (afternoonEnd) afternoonEnd.style.display = 'block';
        if (afternoonStartInput) afternoonStartInput.setAttribute('required', 'required');
        if (afternoonEndInput) afternoonEndInput.setAttribute('required', 'required');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Reset modal when "Add New Event" button is clicked
    const addBtn = document.querySelector('[data-tw-target="#add-product-modal"]');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const saveBtn = document.getElementById('save-product-btn');
            saveBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Event';
            document.getElementById('add-product-form').reset();
            toggleDatetimeFields('', 'add'); // Hide all fields
        });
    }
    
    // Handle schedule type change for Add modal
    const addScheduleType = document.getElementById('add-event-schedule-type');
    if (addScheduleType) {
        addScheduleType.addEventListener('change', function() {
            toggleDatetimeFields(this.value, 'add');
        });
    }
    
    // Handle schedule type change for Edit modal
    const editScheduleType = document.getElementById('edit-event-schedule-type');
    if (editScheduleType) {
        editScheduleType.addEventListener('change', function() {
            toggleDatetimeFields(this.value, 'edit');
        });
    }
    
    // Client-side datetime validation function
    function validateDatetimeFields(form, scheduleType) {
        const prefix = form.id === 'add-product-form' ? 'add' : 'edit';
        const errors = [];
        
        // Helper function to compare datetime-local values
        const compareDates = (dateStr1, dateStr2) => {
            // datetime-local format is YYYY-MM-DDTHH:mm
            // Parse as local time for accurate comparison
            const date1 = new Date(dateStr1 + ':00'); // Add seconds
            const date2 = new Date(dateStr2 + ':00'); // Add seconds
            return date1.getTime() - date2.getTime();
        };
        
        if (scheduleType === 'whole_day') {
            const morningStart = document.getElementById(`${prefix}-start-datetime-morning`);
            const morningEnd = document.getElementById(`${prefix}-end-datetime-morning`);
            const afternoonStart = document.getElementById(`${prefix}-start-datetime-afternoon`);
            const afternoonEnd = document.getElementById(`${prefix}-end-datetime-afternoon`);
            
            if (morningStart && morningEnd && morningStart.value && morningEnd.value) {
                if (compareDates(morningEnd.value, morningStart.value) <= 0) {
                    errors.push('Morning end time must be after morning start time.');
                }
            }
            
            if (morningEnd && afternoonStart && morningEnd.value && afternoonStart.value) {
                if (compareDates(afternoonStart.value, morningEnd.value) <= 0) {
                    errors.push('Afternoon start time must be after morning end time.');
                }
            }
            
            if (afternoonStart && afternoonEnd && afternoonStart.value && afternoonEnd.value) {
                if (compareDates(afternoonEnd.value, afternoonStart.value) <= 0) {
                    errors.push('Afternoon end time must be after afternoon start time.');
                }
            }
        } else if (scheduleType === 'half_day_morning') {
            const morningStart = document.getElementById(`${prefix}-start-datetime-morning`);
            const morningEnd = document.getElementById(`${prefix}-end-datetime-morning`);
            
            if (morningStart && morningEnd && morningStart.value && morningEnd.value) {
                if (compareDates(morningEnd.value, morningStart.value) <= 0) {
                    errors.push('Morning end time must be after morning start time.');
                }
            }
        } else if (scheduleType === 'half_day_afternoon') {
            const afternoonStart = document.getElementById(`${prefix}-start-datetime-afternoon`);
            const afternoonEnd = document.getElementById(`${prefix}-end-datetime-afternoon`);
            
            if (afternoonStart && afternoonEnd && afternoonStart.value && afternoonEnd.value) {
                if (compareDates(afternoonEnd.value, afternoonStart.value) <= 0) {
                    errors.push('Afternoon end time must be after afternoon start time.');
                }
            }
        }
        
        return errors;
    }
    
    // Handle Save Event Button
    const saveEventBtn = document.getElementById('save-product-btn');
    const addEventForm = document.getElementById('add-product-form');
    
    if (saveEventBtn) {
        saveEventBtn.addEventListener('click', function(e) {
            // Prevent multiple submissions
            if (saveEventBtn.disabled) return;
            
            // Get schedule type first
            const scheduleType = document.getElementById('add-event-schedule-type')?.value;
            
            // Validate datetime fields
            const datetimeErrors = validateDatetimeFields(addEventForm, scheduleType);
            if (datetimeErrors.length > 0) {
                // Show unique errors only
                const uniqueErrors = [...new Set(datetimeErrors)];
                showError('Validation Error', uniqueErrors.join('\n'));
                addEventForm.reportValidity();
                return;
            }
            
            // Validate form
            if (addEventForm.checkValidity()) {
                // Get form data
                const formData = new FormData(addEventForm);
                
                // Log form data for debugging
                console.log('Event Data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, ':', value);
                }
                
                // Show loading state
                saveEventBtn.disabled = true;
                saveEventBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Saving...';
                
                // Make AJAX call to save event
                fetch('/events/store', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(async response => {
                    if (!response.ok) {
                        const text = await response.text();
                        console.error('Error response:', text);
                        throw new Error('Server error');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showSuccess('Success!', data.message);
                        // Reset form and close modal
                        addEventForm.reset();
                        const modal = document.getElementById('add-product-modal');
                        if (modal) {
                            modal.classList.remove('show');
                            document.body.classList.remove('modal-open');
                        }
                        // Reload page to refresh list
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showError('Error!', data.message || 'Failed to add event.');
                    }
                })
                .catch(error => {
                    showError('Error!', 'Failed to add event. Please try again.');
                    console.error('Error:', error);
                })
                .finally(() => {
                    saveEventBtn.disabled = false;
                    saveEventBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Event';
                });
                
            } else {
                // Show validation error
                showError('Error!', 'Please fill in all required fields.');
                addEventForm.reportValidity();
            }
        });
    }
});

// Helper to init Tom Select safely
function initTomSelect(selector, options = {}) {
    const el = document.querySelector(selector);
    if (!el) return;
    try {
        if (el.tomselect) {
            return el.tomselect; // reuse instance
        }
        if (window.TomSelect) {
            return new TomSelect(el, Object.assign({
                copyClassesToDropdown: false,
                dropdownParent: 'body',
                render: {
                    option: function(data, escape) {
                        return '<div>' + escape(data.text) + '</div>';
                    }
                }
            }, options));
        }
    } catch (e) {
        console.warn('Tom Select init failed:', e);
    }
}

// Set options for a Tom Select; items = [{id, text}] or [{value, text}]
function setTomOptions(selector, items, placeholderLabel, options = {}) {
    const el = document.querySelector(selector);
    if (!el) return;
    // Destroy previous instance if any
    if (el.tomselect) {
        try { el.tomselect.destroy(); } catch (e) {}
    }
    // Normalize to {value,text}
    const opts = (items || []).map(it => ({ value: it.value ?? it.id, text: it.text }));
    // Build native options first so Tom Select reads them
    el.innerHTML = (placeholderLabel ? `<option value="">${placeholderLabel}</option>` : '') +
        opts.map(o => `<option value="${o.value}">${o.text}</option>`).join('');
    // Initialize Tom Select
    initTomSelect(selector, options);
}

// Add Participants - placeholder action
window.addParticipants = function(eventId) {
    // Open modal and load data
    const modalTrigger = document.createElement('div');
    modalTrigger.style.display = 'none';
    modalTrigger.setAttribute('data-tw-toggle', 'modal');
    modalTrigger.setAttribute('data-tw-target', '#add-participants-modal');
    document.body.appendChild(modalTrigger);
    modalTrigger.click();
    setTimeout(() => document.body.removeChild(modalTrigger), 100);

    // Set event id
    const eventIdInput = document.getElementById('ap-events-id');
    if (eventIdInput) eventIdInput.value = eventId;

    // Clear selects
    const collegeSel = document.getElementById('ap-college');
    const programSel = document.getElementById('ap-program');
    const orgSel = document.getElementById('ap-organization');
    const studentsSel = document.getElementById('ap-students');
    [collegeSel, programSel, orgSel, studentsSel].forEach(sel => { if (sel) sel.innerHTML = ''; });

    // Load form data
    fetch(`/events/participants/${eventId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(async r => {
        const text = await r.text();
        let data;
        try { data = JSON.parse(text); } catch (e) { throw new Error(text || 'Invalid JSON'); }
        if (!r.ok || !data.success) throw new Error(data.message || 'Failed to load participants data');
        console.log('Participants form data loaded:', {
            colleges: data.colleges ? data.colleges.length : 0,
            programs: data.programs ? data.programs.length : 0,
            organizations: data.organizations ? data.organizations.length : 0,
            students: data.students ? data.students.length : 0
        });
        // Populate selects via Tom Select API for reliability
        // Add special options: All/None
        const collegesWithAll = [{ id: '', text: 'All' }, ...(data.colleges || [])];
        const programsWithAll = [{ id: '', text: 'All' }, ...(data.programs || [])];
        const organizationsWithSpecial = [{ id: '', text: 'All' }, { id: 'none', text: 'None' }, ...(data.organizations || [])];

        setTomOptions('#ap-college', collegesWithAll, '-- Select College --', {allowEmptyOption: true});
        setTomOptions('#ap-program', programsWithAll, '-- Select Program --', {allowEmptyOption: true});
        setTomOptions('#ap-organization', organizationsWithSpecial, '-- Select Organization --', {allowEmptyOption: true});
        
        // Get event schedule type
        const scheduleType = data.event ? data.event.event_schedule_type : 'whole_day';
        
        // Toggle time fields based on schedule type
        const morningTimeInGroup = document.getElementById('ap-morning-time-in-group');
        const morningTimeOutGroup = document.getElementById('ap-morning-time-out-group');
        const afternoonTimeInGroup = document.getElementById('ap-afternoon-time-in-group');
        const afternoonTimeOutGroup = document.getElementById('ap-afternoon-time-out-group');
        
        // Hide all first
        [morningTimeInGroup, morningTimeOutGroup, afternoonTimeInGroup, afternoonTimeOutGroup].forEach(el => {
            if (el) el.style.display = 'none';
        });
        
        // Show fields based on schedule type
        if (scheduleType === 'whole_day') {
            [morningTimeInGroup, morningTimeOutGroup, afternoonTimeInGroup, afternoonTimeOutGroup].forEach(el => {
                if (el) el.style.display = 'block';
            });
        } else if (scheduleType === 'half_day_morning') {
            if (morningTimeInGroup) morningTimeInGroup.style.display = 'block';
            if (morningTimeOutGroup) morningTimeOutGroup.style.display = 'block';
        } else if (scheduleType === 'half_day_afternoon') {
            if (afternoonTimeInGroup) afternoonTimeInGroup.style.display = 'block';
            if (afternoonTimeOutGroup) afternoonTimeOutGroup.style.display = 'block';
        }
        
        // Prefill defaults
        const defaults = data.defaults || {};
        const setSelectValue = (selector, value) => {
            const el = document.querySelector(selector);
            if (!el) return;
            if (value === null || value === undefined || value === '') return;
            el.value = String(value);
            if (el.tomselect) {
                try { el.tomselect.setValue(String(value), true); } catch (e) {}
            }
        };
        setSelectValue('#ap-college', defaults.college_id ?? '');
        setSelectValue('#ap-program', defaults.program_id ?? '');
        const orgVal = defaults.organization_id === null ? 'none' : (defaults.organization_id ?? '');
        setSelectValue('#ap-organization', orgVal);

        // Prefill time fields
        const penaltyInput = document.getElementById('ap-late-penalty');
        if (penaltyInput && (defaults.late_penalty || defaults.late_penalty === 0)) penaltyInput.value = defaults.late_penalty;
        
        // Normalize to H:i for <input type="time"> to satisfy backend validation
        const toHourMinute = (val) => {
            if (!val) return '';
            // Expect formats like HH:MM or HH:MM:SS → slice first 5 chars
            return String(val).slice(0, 5);
        };
        
        // Prefill morning fields
        const morningTimeIn = document.getElementById('ap-time-in-morning');
        const morningTimeOut = document.getElementById('ap-time-out-morning');
        if (morningTimeIn && defaults.time_in_morning) morningTimeIn.value = toHourMinute(defaults.time_in_morning);
        if (morningTimeOut && defaults.time_out_morning) morningTimeOut.value = toHourMinute(defaults.time_out_morning);
        
        // Prefill afternoon fields
        const afternoonTimeIn = document.getElementById('ap-time-in-afternoon');
        const afternoonTimeOut = document.getElementById('ap-time-out-afternoon');
        if (afternoonTimeIn && defaults.time_in_afternoon) afternoonTimeIn.value = toHourMinute(defaults.time_in_afternoon);
        if (afternoonTimeOut && defaults.time_out_afternoon) afternoonTimeOut.value = toHourMinute(defaults.time_out_afternoon);
        
        // Student selection removed from modal per request; backend will compute by filters
    })
    .catch(err => {
        console.error(err);
        if (typeof showError === 'function') showError('Error', 'Failed to load participants data.');
    });
};

// Save participants config
document.addEventListener('DOMContentLoaded', function() {
    const saveBtn = document.getElementById('ap-save-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            const form = document.getElementById('add-participants-form');
            const formData = new FormData(form);
            fetch('/events/participants/save', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                const json = await response.json().catch(() => ({}));
                if (!response.ok || !json.success) throw new Error(json.message || 'Server error');
                if (typeof showSuccess === 'function') showSuccess('Success', json.message);
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(err => {
                console.error(err);
                if (typeof showError === 'function') showError('Error', err.message || 'Failed to save');
            });
        });
    }
});

// View Event Details
window.viewEvent = function(eventId) {
    // Open modal
    const trigger = document.createElement('div');
    trigger.style.display = 'none';
    trigger.setAttribute('data-tw-toggle', 'modal');
    trigger.setAttribute('data-tw-target', '#view-event-modal');
    document.body.appendChild(trigger);
    trigger.click();
    setTimeout(() => document.body.removeChild(trigger), 100);

    // Clear content
    const setText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val ?? '-';
    };
    setText('ve-name', '-');
    setText('ve-semester', '-');
    setText('ve-start', '-');
    setText('ve-end', '-');
    setText('ve-desc', '-');
    setText('ve-participants', '0');
    setText('ve-late', '-');

    fetch(`/events/details/${eventId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Failed to load details');
        const evt = data.event || {};
        const sem = data.semester || {};
        const late = data.late || {};
        const assignments = data.assignments || [];

        setText('ve-name', evt.event_name);
        setText('ve-semester', sem.semester ? `${sem.school_year} - ${sem.semester}` : (sem.school_year || ''));
        
        // Display datetime based on schedule type
        const scheduleTypeMap = {
            'whole_day': 'Whole Day',
            'half_day_morning': 'Half day Morning',
            'half_day_afternoon': 'Half day Afternoon'
        };
        setText('ve-schedule-type', scheduleTypeMap[evt.event_schedule_type] || '-');
        
        // Format datetime for display
        const formatDateTime = (dt) => {
            if (!dt) return '-';
            return new Date(dt).toLocaleString();
        };
        
        // Build start/end display based on schedule type
        let startDisplay = '-';
        let endDisplay = '-';
        
        if (evt.event_schedule_type === 'whole_day') {
            startDisplay = evt.start_datetime_morning ? `Morning: ${formatDateTime(evt.start_datetime_morning)}` : '-';
            endDisplay = evt.end_datetime_afternoon ? `Afternoon: ${formatDateTime(evt.end_datetime_afternoon)}` : '-';
        } else if (evt.event_schedule_type === 'half_day_morning') {
            startDisplay = evt.start_datetime_morning ? formatDateTime(evt.start_datetime_morning) : '-';
            endDisplay = evt.end_datetime_morning ? formatDateTime(evt.end_datetime_morning) : '-';
        } else if (evt.event_schedule_type === 'half_day_afternoon') {
            startDisplay = evt.start_datetime_afternoon ? formatDateTime(evt.start_datetime_afternoon) : '-';
            endDisplay = evt.end_datetime_afternoon ? formatDateTime(evt.end_datetime_afternoon) : '-';
        }
        
        setText('ve-start', startDisplay);
        setText('ve-end', endDisplay);
        setText('ve-desc', evt.event_description || '-');
        setText('ve-participants', String(data.participants_count || 0));
        // Display late rule based on schedule type
        if (late) {
            let timeIn = null;
            let timeOut = null;
            const scheduleType = evt.event_schedule_type;
            
            if (scheduleType === 'whole_day' || scheduleType === 'half_day_morning') {
                timeIn = late.time_in_morning;
                timeOut = late.time_out_morning;
            } else if (scheduleType === 'half_day_afternoon') {
                timeIn = late.time_in_afternoon;
                timeOut = late.time_out_afternoon;
            } else {
                // Fallback
                timeIn = late.time_in_morning || late.time_in_afternoon;
                timeOut = late.time_out_morning || late.time_out_afternoon;
            }
            
            if (timeIn || timeOut) {
                setText('ve-late', `${timeIn || '--:--'} - ${timeOut || '--:--'} | Penalty: ${late.late_penalty ?? 0}`);
            } else {
                setText('ve-late', 'No late rule');
            }
        } else {
            setText('ve-late', 'No late rule');
        }

        // Render assignments table
        const tb = document.getElementById('ve-assignments');
        if (tb) {
            if (!assignments.length) {
                tb.innerHTML = '<tr><td colspan="4" class="text-center text-slate-500">No assignments</td></tr>';
            } else {
                tb.innerHTML = assignments.map(a => `
                    <tr>
                        <td>${a.college || '-'}</td>
                        <td>${a.program || '-'}</td>
                        <td>${a.organization || '-'}</td>
                        <td class="text-right">${a.participants || 0}</td>
                    </tr>
                `).join('');
            }
        }

        // Render participants list
        const pb = document.getElementById('ve-participants-body');
        const plist = data.participants || [];
        if (pb) {
            if (!plist.length) {
                pb.innerHTML = '<tr><td colspan="5" class="text-center text-slate-500">No participants</td></tr>';
            } else {
                pb.innerHTML = plist.map(p => `
                    <tr>
                        <td>${p.student_name || 'N/A'}</td>
                        <td>${p.id_number || '-'}</td>
                        <td>${p.college || '-'}</td>
                        <td>${p.program || '-'}</td>
                        <td>${p.organization || '-'}</td>
                    </tr>
                `).join('');
            }
        }
    })
    .catch(err => {
        console.error(err);
        if (typeof showError === 'function') showError('Error', err.message || 'Failed to load details');
    });
};

// Edit Event Function
window.editEvent = function(eventId) {
    console.log('Edit button clicked for event ID:', eventId);
    
    fetch(`/events/edit/${eventId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            const event = data.data;
            
            // Trigger Tailwind modal system using the same method as Add button
            const modalTrigger = document.createElement('div');
            modalTrigger.style.display = 'none';
            modalTrigger.setAttribute('data-tw-toggle', 'modal');
            modalTrigger.setAttribute('data-tw-target', '#edit-product-modal');
            document.body.appendChild(modalTrigger);
            
            // Click the trigger to show modal
            modalTrigger.click();
            
            // Clean up
            setTimeout(() => {
                document.body.removeChild(modalTrigger);
            }, 100);
            
            // Wait for modal to show before filling data
            const checkModal = setInterval(() => {
                const modalElement = document.getElementById('edit-product-modal');
                if (modalElement && modalElement.classList.contains('show')) {
                    clearInterval(checkModal);
                    
                    // Fill edit form with existing data after modal is shown
                    setTimeout(() => {
                        const nameInput = document.getElementById('edit-event-name');
                        if (nameInput) {
                            nameInput.value = event.event_name;
                        }
                        
                        const descriptionInput = document.getElementById('edit-event-description');
                        if (descriptionInput) {
                            descriptionInput.value = event.event_description || '';
                        }
                        
                        // Set schedule type first, then toggle fields
                        const scheduleSel = document.getElementById('edit-event-schedule-type');
                        if (scheduleSel && event.event_schedule_type) {
                            scheduleSel.value = event.event_schedule_type;
                            toggleDatetimeFields(event.event_schedule_type, 'edit');
                        }
                        
                        // Format datetime helper
                        const formatDatetime = (dt) => {
                            if (!dt) return '';
                            const d = new Date(dt);
                            return d.toISOString().slice(0, 16);
                        };
                        
                        // Fill datetime fields based on schedule type
                        const morningStartInput = document.getElementById('edit-start-datetime-morning');
                        const morningEndInput = document.getElementById('edit-end-datetime-morning');
                        const afternoonStartInput = document.getElementById('edit-start-datetime-afternoon');
                        const afternoonEndInput = document.getElementById('edit-end-datetime-afternoon');
                        
                        if (morningStartInput && event.start_datetime_morning) {
                            morningStartInput.value = formatDatetime(event.start_datetime_morning);
                        }
                        if (morningEndInput && event.end_datetime_morning) {
                            morningEndInput.value = formatDatetime(event.end_datetime_morning);
                        }
                        if (afternoonStartInput && event.start_datetime_afternoon) {
                            afternoonStartInput.value = formatDatetime(event.start_datetime_afternoon);
                        }
                        if (afternoonEndInput && event.end_datetime_afternoon) {
                            afternoonEndInput.value = formatDatetime(event.end_datetime_afternoon);
                        }
                        
                        const finesInput = document.getElementById('edit-fines');
                        if (finesInput) {
                            finesInput.value = event.fines || '';
                        }
                        
                        // Set radio button based on status
                        if (event.status === 'active') {
                            const activeRadio = document.getElementById('edit-status-active');
                            if (activeRadio) activeRadio.checked = true;
                        } else {
                            const inactiveRadio = document.getElementById('edit-status-inactive');
                            if (inactiveRadio) inactiveRadio.checked = true;
                        }
                        
                        // Set event ID for update button
                        const updateBtn = document.getElementById('update-product-btn');
                        if (updateBtn) {
                            updateBtn.setAttribute('data-event-id', eventId);
                        }
                        // Wire Edit Participants button
                        const editPartBtn = document.getElementById('edit-participants-open-btn');
                        if (editPartBtn) {
                            editPartBtn.setAttribute('data-event-id', eventId);
                            editPartBtn.onclick = function() { addParticipants(eventId); };
                        }
                    }, 100);
                }
            }, 50);
        } else {
            showError('Error!', 'Failed to load event data.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to load event data.');
    });
};

// Update Event Function
window.updateEvent = function(eventId) {
    const form = document.getElementById('edit-product-form');
    const updateBtn = document.getElementById('update-product-btn');
    
    // Prevent multiple submissions
    if (updateBtn && updateBtn.disabled) return;
    
    // Get schedule type first
    const scheduleType = document.getElementById('edit-event-schedule-type')?.value;
    
    // Validate datetime fields
    const datetimeErrors = validateDatetimeFields(form, scheduleType);
    if (datetimeErrors.length > 0) {
        // Show unique errors only
        const uniqueErrors = [...new Set(datetimeErrors)];
        showError('Validation Error', uniqueErrors.join('\n'));
        form.reportValidity();
        return;
    }
    
    // Validate form
    if (!form.checkValidity()) {
        showError('Error!', 'Please fill in all required fields.');
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    
    // Add method spoofing for PUT request
    formData.append('_method', 'PUT');
    
    if (!updateBtn) return;
    updateBtn.disabled = true;
    updateBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 mr-2 animate-spin"></i> Updating...';
    
    fetch(`/events/update/${eventId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(async response => {
        if (!response.ok) {
            const text = await response.text();
            console.error('Error response:', text);
            throw new Error('Server error');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showSuccess('Success!', data.message);
            
            // Close modal
            const modal = document.getElementById('edit-product-modal');
            modal.classList.remove('show');
            modal.setAttribute('style', 'display: none;');
            document.body.classList.remove('modal-open');
            
            // Remove backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            
            // Reset form
            form.reset();
            
            // Reload page
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showError('Error!', data.message || 'Failed to update event.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to update event.');
    })
    .finally(() => {
        updateBtn.disabled = false;
        updateBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4 mr-2"></i> Update Event';
    });
};

// Add event listener for update button
document.addEventListener('DOMContentLoaded', function() {
    const updateBtn = document.getElementById('update-product-btn');
    if (updateBtn) {
        updateBtn.addEventListener('click', function() {
            const eventId = this.getAttribute('data-event-id');
            if (eventId) {
                updateEvent(eventId);
            }
        });
    }
});

// Show Delete Confirmation Modal
window.confirmDelete = function(eventId, eventName) {
    // Set event name in modal
    const nameElement = document.getElementById('delete-event-name');
    if (nameElement) {
        nameElement.textContent = eventName;
    }
    
    // Set event ID for confirm button
    const confirmBtn = document.getElementById('confirm-delete-btn');
    if (confirmBtn) {
        confirmBtn.setAttribute('data-event-id', eventId);
    }
    
    // Trigger Tailwind modal system
    const modalTrigger = document.createElement('div');
    modalTrigger.style.display = 'none';
    modalTrigger.setAttribute('data-tw-toggle', 'modal');
    modalTrigger.setAttribute('data-tw-target', '#delete-confirmation-modal');
    document.body.appendChild(modalTrigger);
    
    // Click the trigger to show modal
    modalTrigger.click();
    
    // Clean up
    setTimeout(() => {
        document.body.removeChild(modalTrigger);
    }, 100);
};

// Delete Event Function
window.deleteEvent = function(eventId) {
    fetch(`/events/delete/${eventId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Success!', data.message);
            
            // Close modal
            const modal = document.getElementById('delete-confirmation-modal');
            if (modal) {
                modal.classList.remove('show');
                modal.setAttribute('style', 'display: none;');
                document.body.classList.remove('modal-open');
            }
            
            // Remove backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showError('Error!', data.message || 'Failed to delete event.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error!', 'Failed to delete event.');
    });
};

// Add event listener for confirm delete button
document.addEventListener('DOMContentLoaded', function() {
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const eventId = this.getAttribute('data-event-id');
            if (eventId) {
                deleteEvent(eventId);
            }
        });
    }
});



