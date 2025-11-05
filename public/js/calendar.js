"use strict";

// ALLOW calendar initialization now (override prevention)
// Set this IMMEDIATELY before document.ready to allow our calendar
window.allowCalendarInit = true;
window.preventTemplateCalendar = false;

// Initialize calendar using FullCalendar 3.x template style
$(document).ready(function() {
    // Check if calendar instance actually exists
    var $calendarEl = $('#calendar');
    var hasCalendarInstance = false;
    try {
        var calData = $calendarEl.data('fullCalendar');
        hasCalendarInstance = calData !== undefined && calData !== null;
    } catch(e) {
        hasCalendarInstance = false;
    }
    
    // Only skip if calendar instance ACTUALLY exists
    if (hasCalendarInstance) {
        console.log('Calendar instance already exists, skipping initialization...');
        return;
    }
    
    // Clear any flags - they may have been set incorrectly
    window.calendarInitialized = false;
    window.calendarInstanceCreated = false;
    console.log('Initializing calendar...');
    
    // Check if jQuery and FullCalendar are available
    if (typeof jQuery === 'undefined' || typeof jQuery.fn === 'undefined' || typeof jQuery.fn.fullCalendar === 'undefined') {
        console.error('jQuery or FullCalendar not available');
        return;
    }
    
    // Check if calendar element exists
    if (!$('#calendar').length) {
        console.error('Calendar element not found');
        return;
    }
    
    // FORCE destroy ANY existing calendar instances first (including template/demo calendars)
    // This prevents template calendar from showing before our calendar loads
    try {
        var $calendarEl = $('#calendar');
        
        // Check if calendar exists via data attribute (template calendar might already be initialized)
        if ($calendarEl.data('fullCalendar')) {
            console.log('Destroying existing template/demo calendar instance...');
            try {
                $calendarEl.fullCalendar('destroy');
            } catch(e) {
                console.log('Error destroying calendar:', e);
            }
            $calendarEl.empty();
            
            // Force clear any FullCalendar data
            $calendarEl.removeData('fullCalendar');
            
            // Wait a bit for destruction to complete
            setTimeout(function() {
                initializeCalendar();
            }, 200);
            return;
        }
        
        // Also check if calendar HTML exists (might be from template initialization)
        var calendarHTML = $calendarEl.html().trim();
        if (calendarHTML !== '') {
            console.log('Clearing template calendar HTML content...');
            $calendarEl.empty();
            // Also check for FullCalendar DOM elements
            $calendarEl.find('.fc').remove();
            $calendarEl.find('.fc-calendar').remove();
        }
    } catch(e) {
        console.log('Error destroying template calendar:', e);
    }
    
    // Mark as initialized IMMEDIATELY to prevent other initializations
    window.calendarInitialized = true;
    window.calendarInstanceCreated = true;
    
    // Initialize calendar
    initializeCalendar();
});

function initializeCalendar() {
    var $calendarEl = $('#calendar');
    
    // FINAL check - destroy any remaining calendar instances
    if ($calendarEl.data('fullCalendar')) {
        console.log('Calendar still exists, force destroying...');
        try {
            $calendarEl.fullCalendar('destroy');
            $calendarEl.empty();
            $calendarEl.removeData('fullCalendar');
        } catch(e) {
            console.log('Force destroy error:', e);
            // Manual cleanup if destroy fails
            $calendarEl.empty();
            $calendarEl.find('.fc').remove();
        }
        // Wait before re-initializing
        setTimeout(function() {
            createCalendar();
        }, 150);
        return;
    }
    
    // Double check - clear any FullCalendar DOM elements
    $calendarEl.find('.fc').remove();
    $calendarEl.find('.fc-calendar').remove();
    
    // Create calendar
    createCalendar();
}

function createCalendar() {
    var $calendarEl = $('#calendar');
    
    // Get events data from window object (set in blade file)
    var eventsData = window.calendarEventsData || [];
    console.log('Calendar events data loaded:', eventsData);
    
    // Determine initial date: use earliest event date if events exist, otherwise use current date
    var initialDate = new Date();
    if (eventsData && eventsData.length > 0) {
        var earliestDate = null;
        eventsData.forEach(function(event) {
            if (event.start) {
                var eventDate = new Date(event.start);
                if (!earliestDate || eventDate < earliestDate) {
                    earliestDate = eventDate;
                }
            }
        });
        if (earliestDate) {
            initialDate = earliestDate;
        }
    }
    
    // Format initial date as YYYY-MM-DD for FullCalendar 3.x
    function pad(num) {
        return (num < 10 ? '0' : '') + num;
    }
    var initialDateStr = initialDate.getFullYear() + '-' + 
                        pad(initialDate.getMonth() + 1) + '-' + 
                        pad(initialDate.getDate());
    
    // Format events data for FullCalendar 3.x (exact template style)
    var formattedEvents = eventsData.map(function(event) {
        // Use template blue color - matching template calendar.js
        return {
            title: event.title || '',
            start: event.start || null,
            end: event.end || null,
            borderColor: '#4680ff',
            backgroundColor: '#4680ff',
            textColor: '#fff',
            editable: true
        };
    });
    
    // Remove events with null start dates
    formattedEvents = formattedEvents.filter(function(event) {
        return event.start !== null;
    });
    
    // Initialize FullCalendar using template style - exact match to template
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay,listMonth'
        },
        defaultDate: initialDateStr,
        navLinks: true, // can click day/week names to navigate views
        businessHours: true, // display business hours
        editable: true,
        droppable: true, // this allows things to be dropped onto the calendar
        events: formattedEvents,
        drop: function() {
            // is the "remove after drop" checkbox checked?
            if ($('#checkbox-events').is(':checked')) {
                // if so, remove the element from the "Draggable Events" list
                $(this).remove();
            }
        }
    });
    
    console.log('FullCalendar initialized successfully using template style');
}
