@extends('layouts.master')

@section('subcontent')
<div class="intro-y flex flex-col sm:flex-row items-center mt-8 col-span-12">
                    <h2 class="text-lg font-medium mr-auto">
                        Calendar
                    </h2>
                   
                </div>
                <div class="grid grid-cols-12 gap-5 mt-5 col-span-12">
                    <!-- BEGIN: Calendar Side Menu -->
                    <!-- <div class="col-span-12 xl:col-span-4 2xl:col-span-3">
                        <div class="box p-5 intro-y">
                            <button type="button" class="btn btn-primary w-full mt-2"> <i class="w-4 h-4 mr-2" data-lucide="edit-3"></i> Add New Schedule </button>
                            <div class="border-t border-b border-slate-200/60 dark:border-darkmode-400 mt-6 mb-5 py-3" id="calendar-events">
                                @forelse($events as $event)
                                    @php
                                        // Determine start datetime based on schedule type
                                        $startDatetime = null;
                                        $endDatetime = null;
                                        $displayTime = '';
                                        
                                        if ($event->event_schedule_type === 'whole_day') {
                                            $startDatetime = $event->start_datetime_morning;
                                            $endDatetime = $event->end_datetime_afternoon;
                                            if ($startDatetime) {
                                                $displayTime = \Carbon\Carbon::parse($startDatetime)->format('h:i A');
                                            }
                                        } elseif ($event->event_schedule_type === 'half_day_morning') {
                                            $startDatetime = $event->start_datetime_morning;
                                            $endDatetime = $event->end_datetime_morning;
                                            if ($startDatetime) {
                                                $displayTime = \Carbon\Carbon::parse($startDatetime)->format('h:i A');
                                            }
                                        } elseif ($event->event_schedule_type === 'half_day_afternoon') {
                                            $startDatetime = $event->start_datetime_afternoon;
                                            $endDatetime = $event->end_datetime_afternoon;
                                            if ($startDatetime) {
                                                $displayTime = \Carbon\Carbon::parse($startDatetime)->format('h:i A');
                                            }
                                        }
                                        
                                        // Calculate days difference
                                        $days = 0;
                                        if ($startDatetime && $endDatetime) {
                                            $start = \Carbon\Carbon::parse($startDatetime);
                                            $end = \Carbon\Carbon::parse($endDatetime);
                                            $days = max(1, $start->diffInDays($end) + 1);
                                        } elseif ($startDatetime) {
                                            $days = 1;
                                        }
                                    @endphp
                                    @if($startDatetime)
                                    <div class="relative" data-event-id="{{ $event->id }}">
                                        <div class="event p-3 -mx-3 cursor-pointer transition duration-300 ease-in-out hover:bg-slate-100 dark:hover:bg-darkmode-400 rounded-md flex items-center">
                                            <div class="w-2 h-2 bg-pending rounded-full mr-3"></div>
                                            <div class="pr-10">
                                                <div class="event__title truncate">{{ $event->event_name }}</div>
                                                <div class="text-slate-500 text-xs mt-0.5"> 
                                                    <span class="event__days">{{ $days }}</span> 
                                                    {{ $days > 1 ? 'Days' : 'Day' }} 
                                                    @if($displayTime)
                                                    <span class="mx-1">•</span> {{ $displayTime }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <a class="flex items-center absolute top-0 bottom-0 my-auto right-0" href="javascript:void(0)"> <i data-lucide="edit" class="w-4 h-4 text-slate-500"></i> </a>
                                    </div>
                                    @endif
                                @empty
                                    <div class="text-slate-500 p-3 text-center" id="calendar-no-events">No events yet</div>
                                @endforelse
                            </div>
                            
                        </div>
                       
                    </div> -->
                    <!-- END: Calendar Side Menu -->
                    <!-- BEGIN: Calendar Content -->
                    <div class="col-span-12 xl:col-span-8 2xl:col-span-12">
                        <div class="box p-5">
                            <div class="full-calendar custom-calendar" id="calendar"></div>
                        </div>
                    </div>
                    <!-- END: Calendar Content -->
                </div>
            </div>
            <!-- END: Content -->
<!-- END: Delete Confirmation Modal -->
@push('scripts')
<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.css" rel="stylesheet" />
<!-- Template FullCalendar CSS -->
<link href="{{ asset('files/assets/css/style.css') }}" rel="stylesheet" />
<style>
/* Make ALL FullCalendar buttons visible - override template CSS */
.fc-toolbar button,
.fc-button,
.fc-button-primary,
.fc-prev-button,
.fc-next-button,
.fc-today-button {
    background-color: #fff !important;
    color: #272727 !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    padding: 8px 16px !important;
    font-weight: 500 !important;
    text-shadow: none !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
    background-image: none !important;
    cursor: pointer !important;
    display: inline-block !important;
    opacity: 1 !important;
}

.fc-toolbar button:hover,
.fc-button:hover,
.fc-button-primary:hover,
.fc-prev-button:hover,
.fc-next-button:hover,
.fc-today-button:hover {
    background-color: #f3f4f6 !important;
    border-color: #9ca3af !important;
    color: #272727 !important;
}

.fc-toolbar button:active,
.fc-button:active,
.fc-button-primary:active {
    background-color: #e5e7eb !important;
}

/* Icons inside buttons - make chevrons very visible */
.fc-button .fc-icon,
.fc-prev-button .fc-icon,
.fc-next-button .fc-icon,
.fc-today-button .fc-icon {
    color: #1f2937 !important;
    opacity: 1 !important;
    visibility: visible !important;
    display: inline-block !important;
    font-size: 18px !important;
    line-height: 1 !important;
    width: auto !important;
    height: auto !important;
    font-weight: 700 !important;
}

/* Ensure icon elements are visible */
.fc-icon,
.fc-icon-left-single-arrow,
.fc-icon-right-single-arrow {
    color: #1f2937 !important;
    opacity: 1 !important;
    visibility: visible !important;
    display: inline-block !important;
    font-size: 18px !important;
    font-weight: bold !important;
}

/* Make sure button text and icons are not hidden */
.fc-prev-button,
.fc-next-button {
    text-indent: 0 !important;
    overflow: visible !important;
}

.fc-prev-button .fc-icon,
.fc-next-button .fc-icon {
    display: inline !important;
    visibility: visible !important;
    opacity: 1 !important;
    color: #1f2937 !important;
    font-size: 20px !important;
    font-weight: 900 !important;
    line-height: 1 !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* View buttons - active state */
.fc-button-primary.fc-state-active,
.fc-button-primary.fc-button-active {
    background-color: #01a9ac !important;
    color: #fff !important;
    border-color: #01a9ac !important;
}

.fc-button-primary.fc-state-active .fc-icon,
.fc-button-primary.fc-button-active .fc-icon {
    color: #fff !important;
}

/* Override template CSS that might hide buttons */
.fc-state-default {
    background-color: #fff !important;
    color: #272727 !important;
    border-color: #d1d5db !important;
}

.fc-state-default.fc-button {
    background-color: #fff !important;
    color: #272727 !important;
}
</style>

<script>
    // PREVENT TEMPLATE CALENDAR INITIALIZATION - RUN IMMEDIATELY
    // This must run BEFORE any other scripts to block template calendar
    (function() {
        // Set flags to prevent template calendar
        window.calendarInitialized = false;
        window.calendarInstanceCreated = false;
        window.calendarJSLoaded = false;
        window.preventTemplateCalendar = true;
        window.allowCalendarInit = false;
        
        // Continuously monitor and destroy template calendars
        function destroyTemplateCalendar() {
            if (typeof jQuery !== 'undefined' && jQuery('#calendar').length) {
                var $cal = jQuery('#calendar');
                
                // Destroy any calendar instance
                if ($cal.data('fullCalendar')) {
                    // Only destroy if it's NOT our calendar (check for our events data)
                    var hasOurEvents = window.calendarEventsData && window.calendarEventsData.length > 0;
                    var calEvents = null;
                    try {
                        var cal = $cal.fullCalendar('getCalendar');
                        if (cal) {
                            calEvents = cal.getEvents();
                        }
                    } catch(e) {}
                    
                    // If calendar doesn't have our events, destroy it (it's the template)
                    if (!hasOurEvents || (calEvents && calEvents.length > 0 && !calEvents.some(function(e) {
                        return window.calendarEventsData && window.calendarEventsData.some(function(ourEvent) {
                            return ourEvent.title === e.title;
                        });
                    }))) {
                        console.log('Destroying template calendar...');
                        try {
                            $cal.fullCalendar('destroy');
                            $cal.empty();
                            $cal.removeData('fullCalendar');
                        } catch(e) {
                            $cal.empty();
                            $cal.find('.fc').remove();
                        }
                    }
                }
            }
        }
        
        // Monitor continuously until our calendar is loaded
        var destroyInterval = setInterval(function() {
            if (typeof jQuery !== 'undefined' && jQuery('#calendar').length) {
                var $cal = jQuery('#calendar');
                var hasCalendar = $cal.data('fullCalendar') !== undefined && $cal.data('fullCalendar') !== null;
                
                // Only destroy template calendars if our calendar isn't initialized yet
                if (!window.calendarInitialized && !hasCalendar && window.preventTemplateCalendar) {
                    destroyTemplateCalendar();
                } else if (hasCalendar || window.calendarInitialized) {
                    clearInterval(destroyInterval);
                }
            }
        }, 100);
        
        // Also intercept fullCalendar function when jQuery loads
        function interceptFullCalendar() {
            if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.fullCalendar) {
                var originalFullCalendar = jQuery.fn.fullCalendar;
                
                // Wrap to block template initialization
                jQuery.fn.fullCalendar = function(options) {
                    // Check if this is being called on #calendar
                    if (this.selector === '#calendar' || (this.length && this[0] && this[0].id === 'calendar')) {
                        // Only allow if we explicitly permit it
                        if (!window.allowCalendarInit && window.preventTemplateCalendar) {
                            console.log('Blocked template calendar initialization on #calendar');
                            // Still destroy it to be safe
                            setTimeout(function() {
                                if (jQuery('#calendar').data('fullCalendar')) {
                                    jQuery('#calendar').fullCalendar('destroy');
                                    jQuery('#calendar').empty();
                                }
                            }, 50);
                            return this;
                        }
                    }
                    
                    // Allow for other elements or if allowed
                    return originalFullCalendar.apply(this, arguments);
                };
                
                console.log('FullCalendar function intercepted');
            } else {
                setTimeout(interceptFullCalendar, 50);
            }
        }
        
        // Start intercepting
        interceptFullCalendar();
        
        // Also destroy on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(destroyTemplateCalendar, 100);
            });
        } else {
            setTimeout(destroyTemplateCalendar, 100);
        }
    })();
    
    // Set calendar events data - MUST be set before calendar.js loads
    window.calendarEventsData = {!! json_encode($calendarEvents) !!};
    console.log('Calendar events data loaded:', window.calendarEventsData);
    console.log('Number of events:', window.calendarEventsData ? window.calendarEventsData.length : 0);
    
    // Log event details for debugging
    if (window.calendarEventsData && window.calendarEventsData.length > 0) {
        window.calendarEventsData.forEach(function(event, index) {
            console.log('Event ' + (index + 1) + ':', {
                title: event.title,
                start: event.start,
                end: event.end,
                allDay: event.allDay
            });
        });
    }
    
    // Load scripts in correct order: jQuery -> FullCalendar -> calendar.js
    (function() {
        function loadScript(src, callback) {
            var script = document.createElement('script');
            script.src = src;
            script.async = false;
            script.onload = callback;
            script.onerror = function() {
                console.error('Failed to load script:', src);
            };
            document.head.appendChild(script);
        }
        
        function loadCalendarJS() {
            // Prevent loading calendar.js multiple times - STRICT CHECK
            if (window.calendarJSLoaded || window.calendarInitialized || window.calendarInstanceCreated) {
                console.log('calendar.js already processed, skipping...');
                return;
            }
            
            // FORCE clear ANY template calendars BEFORE loading our script
            try {
                if (typeof jQuery !== 'undefined' && jQuery('#calendar').length) {
                    var $cal = jQuery('#calendar');
                    
                    // Destroy any existing calendar (including template calendars)
                    if ($cal.data('fullCalendar')) {
                        console.log('Force destroying template/existing calendar BEFORE loading our script...');
                        try {
                            $cal.fullCalendar('destroy');
                        } catch(e) {
                            console.log('Destroy error:', e);
                        }
                    }
                    
                    // Force clear HTML and FullCalendar DOM elements
                    $cal.empty();
                    $cal.find('.fc').remove();
                    $cal.find('.fc-calendar').remove();
                    $cal.removeData('fullCalendar');
                    
                    console.log('Template calendar cleared');
                }
            } catch(e) {
                console.log('Error cleaning template calendar:', e);
            }
            
            // Mark as loading IMMEDIATELY before script tag creation
            window.calendarJSLoaded = true;
            
            // Load calendar.js ONLY ONCE
            var scriptEl = document.querySelector('script[src*="calendar.js"]');
            if (scriptEl) {
                console.log('calendar.js script tag already exists, not loading again');
                return;
            }
            
            loadScript('{{ asset("js/calendar.js") }}?v={{ time() }}', function() {
                console.log('calendar.js loaded successfully - SINGLE LOAD');
            });
        }
        
        function loadFullCalendar() {
            // Verify jQuery is available before loading FullCalendar
            if (typeof jQuery === 'undefined' || typeof jQuery.fn === 'undefined') {
                console.error('jQuery not available when loading FullCalendar');
                // Retry
                setTimeout(function() {
                    if (typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined') {
                        loadFullCalendar();
                    }
                }, 100);
                return;
            }
            
            // Check if Moment.js is needed and available
            // FullCalendar 3.x requires Moment.js
            if (typeof moment === 'undefined') {
                console.log('Moment.js not found, loading Moment.js first...');
                loadScript('https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js', function() {
                    console.log('Moment.js loaded');
                    // Now load FullCalendar
                    setTimeout(function() {
                        loadFullCalendarScript();
                    }, 100);
                });
                return;
            }
            
            // Moment.js is available, load FullCalendar
            loadFullCalendarScript();
        }
        
        function loadFullCalendarScript() {
            console.log('jQuery and Moment.js confirmed ready, loading FullCalendar...');
            // Load FullCalendar after jQuery and Moment.js are confirmed ready
            loadScript('https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/fullcalendar.min.js', function() {
                console.log('FullCalendar script loaded');
                // Wait for FullCalendar to register as jQuery plugin
                setTimeout(function() {
                    if (typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined' && typeof jQuery.fn.fullCalendar !== 'undefined') {
                        console.log('FullCalendar jQuery plugin registered successfully');
                        loadCalendarJS();
                    } else {
                        console.error('FullCalendar jQuery plugin not found after load');
                        console.log('jQuery:', typeof jQuery);
                        console.log('jQuery.fn:', typeof jQuery !== 'undefined' ? typeof jQuery.fn : 'undefined');
                        console.log('Moment.js:', typeof moment !== 'undefined');
                        console.log('jQuery.fn.fullCalendar:', typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined' ? typeof jQuery.fn.fullCalendar : 'undefined');
                    }
                }, 300);
            });
        }
        
        function loadjQuery() {
            // Check if jQuery is already loaded (might be from app.js or other sources)
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined') {
                console.log('jQuery already available');
                // Ensure it's globally accessible
                if (typeof window.jQuery === 'undefined') {
                    window.jQuery = jQuery;
                }
                if (typeof window.$ === 'undefined') {
                    window.$ = jQuery;
                }
                setTimeout(loadFullCalendar, 100);
                return;
            }
            
            // Load jQuery first
            console.log('Loading jQuery...');
            loadScript('https://code.jquery.com/jquery-3.6.0.min.js', function() {
                console.log('jQuery script loaded');
                // Wait for jQuery to be fully initialized
                setTimeout(function() {
                    if (typeof jQuery !== 'undefined' && typeof jQuery.fn !== 'undefined') {
                        // Ensure jQuery is globally accessible
                        window.jQuery = jQuery;
                        window.$ = jQuery;
                        console.log('jQuery ready and set to global');
                        loadFullCalendar();
                    } else {
                        console.error('jQuery not properly initialized after script load');
                    }
                }, 150);
            });
        }
        
        // Start loading when ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(loadjQuery, 100);
            });
        } else {
            setTimeout(loadjQuery, 100);
        }
    })();
</script>
@endpush
@endsection
