// Define the calendar variable in the global scope
var _token = $('meta[name="csrf-token"]').attr('content');

var selectedResource;
var selectedYear;
var selectedMonth;

import { Calendar } from '@fullcalendar/core';
import interactionPlugin, { Draggable } from '@fullcalendar/interaction';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';


$(document).ready(function () {

    NewFullCalendarInstance();

});


function NewFullCalendarInstance(){

    if ($("#calendar").length) {
        if ($("#calendar-events").length) {
            new Draggable($("#calendar-events")[0], {
                itemSelector: ".event",
                eventData: function eventData(eventEl) {
                    return {
                        title: $(eventEl).find(".event__title").html(),
                        duration: {
                            days: parseInt($(eventEl).find(".event__days").text())
                        }
                    };
                }
            });
        }

        // Get events data from window object (set in blade file)
        var eventsData = window.calendarEventsData || [];
        console.log('Calendar events data loaded:', eventsData);

        // Determine initial date: use earliest event date if events exist, otherwise use current date
        var initialDate = new Date();
        if (eventsData && eventsData.length > 0) {
            // Find the earliest event date
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

        var calendar = new Calendar($("#calendar")[0], {
            plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin, listPlugin],
            droppable: true,
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek"
            },
            initialDate: initialDate, // Set to earliest event date or current date
            navLinks: true,
            editable: true,
            dayMaxEvents: true,
            events: eventsData,
            drop: function drop(info) {
                if ($("#checkbox-events").length && $("#checkbox-events")[0].checked) {
                    $(info.draggedEl).parent().remove();

                    if ($("#calendar-events").children().length == 1) {
                        $("#calendar-no-events").removeClass("hidden");
                    }
                }
            }
        });
        calendar.render();
    }

}

