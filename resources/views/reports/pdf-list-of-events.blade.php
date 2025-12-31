<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Events</title>
    <style>
        @page {
            margin-top: 10mm;
            margin-right: 15mm;
            margin-bottom: 10mm;
            margin-left: 15mm;
            size: A4 landscape;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            color: #000;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        .container {
            width: 100%;
            padding: 5px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .header p {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }
        .table-container {
            margin: 10px 0;
        }
        table {
            width: 96%;
            border-collapse: collapse;
            margin: 0 auto;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #333;
            padding: 5px 3px;
            text-align: left;
            line-height: 1.3;
            word-wrap: break-word;
            overflow: hidden;
        }
        th {
            background-color: #e8e8e8;
            font-weight: bold;
            font-size: 7px;
            text-transform: uppercase;
        }
        td {
            font-size: 7px;
            vertical-align: top;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            padding-left: 2%;
            text-align: left;
            font-size: 8px;
            color: #333;
            font-weight: bold;
            width: 96%;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>LIST OF EVENTS</h1>
            <p>Generated on: {{ date('F d, Y h:i A') }}</p>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 20%;">Event Name</th>
                        <th style="width: 25%;">Description</th>
                        <th style="width: 10%;">Semester</th>
                        <th style="width: 15%;">Schedule Type</th>
                        <th style="width: 15%;">Date/Time</th>
                        <th style="width: 8%;">Fines</th>
                        <th style="width: 7%;">Participants</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                    <tr>
                        <td>{{ $event->event_name }}</td>
                        <td>{{ $event->event_description ?? 'N/A' }}</td>
                        <td>{{ $event->semester ? $event->semester->semester : 'N/A' }}</td>
                        <td>
                            @if($event->event_schedule_type == 'whole_day')
                                Whole Day
                            @elseif($event->event_schedule_type == 'half_day_morning')
                                Half Day (Morning)
                            @elseif($event->event_schedule_type == 'half_day_afternoon')
                                Half Day (Afternoon)
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($event->event_schedule_type == 'whole_day')
                                {{ $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning)->format('M d, Y') : 'N/A' }}
                            @elseif($event->event_schedule_type == 'half_day_morning')
                                {{ $event->start_datetime_morning ? \Carbon\Carbon::parse($event->start_datetime_morning)->format('M d, Y h:i A') : 'N/A' }}
                            @elseif($event->event_schedule_type == 'half_day_afternoon')
                                {{ $event->start_datetime_afternoon ? \Carbon\Carbon::parse($event->start_datetime_afternoon)->format('M d, Y h:i A') : 'N/A' }}
                            @endif
                        </td>
                        <td class="text-center">Php {{ number_format($event->fines ?? 0, 2) }}</td>
                        <td class="text-center">{{ $event->participants_count }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 20px;">No events found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>Total Events: {{ $events->count() }}</p>
        </div>
    </div>
</body>
</html>

