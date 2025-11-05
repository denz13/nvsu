<?php

namespace App\Http\Controllers\calendar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\events;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function calendar()
    {
        // Fetch all active events
        $events = events::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        // Format events for FullCalendar
        $calendarEvents = $events->map(function($event) {
            $eventData = [
                'id' => $event->id,
                'title' => $event->event_name,
                'description' => $event->event_description ?? '',
                'status' => $event->status,
            ];

            // Helper function to validate and parse datetime
            $parseDateTime = function($datetime) {
                if (!$datetime) {
                    return null;
                }
                try {
                    $parsed = Carbon::parse($datetime);
                    // Check if date is reasonable (not before 2000 and not too far in future)
                    if ($parsed->year < 2000 || $parsed->year > 2100) {
                        return null;
                    }
                    return $parsed->format('Y-m-d\TH:i:s');
                } catch (\Exception $e) {
                    return null;
                }
            };

            // Set start and end dates based on schedule type
            if ($event->event_schedule_type === 'whole_day') {
                // Whole day event: use morning start and afternoon end
                $startMorning = $parseDateTime($event->start_datetime_morning);
                $endAfternoon = $parseDateTime($event->end_datetime_afternoon);
                
                if ($startMorning && $endAfternoon) {
                    $eventData['start'] = $startMorning;
                    $eventData['end'] = $endAfternoon;
                    $eventData['allDay'] = false;
                } elseif ($startMorning) {
                    $eventData['start'] = $startMorning;
                    $eventData['allDay'] = true;
                }
            } elseif ($event->event_schedule_type === 'half_day_morning') {
                // Half day morning: use morning start and end
                $startMorning = $parseDateTime($event->start_datetime_morning);
                $endMorning = $parseDateTime($event->end_datetime_morning);
                
                if ($startMorning && $endMorning) {
                    $eventData['start'] = $startMorning;
                    $eventData['end'] = $endMorning;
                    $eventData['allDay'] = false;
                } elseif ($startMorning) {
                    $eventData['start'] = $startMorning;
                    $eventData['allDay'] = true;
                }
            } elseif ($event->event_schedule_type === 'half_day_afternoon') {
                // Half day afternoon: use afternoon start and end
                $startAfternoon = $parseDateTime($event->start_datetime_afternoon);
                $endAfternoon = $parseDateTime($event->end_datetime_afternoon);
                
                if ($startAfternoon && $endAfternoon) {
                    $eventData['start'] = $startAfternoon;
                    $eventData['end'] = $endAfternoon;
                    $eventData['allDay'] = false;
                } elseif ($startAfternoon) {
                    $eventData['start'] = $startAfternoon;
                    $eventData['allDay'] = true;
                }
            }

            return $eventData;
        })->filter(function($event) {
            // Filter out events without valid start dates
            return isset($event['start']);
        });

        return view('calendar.calendar', compact('events', 'calendarEvents'));
    }
}
