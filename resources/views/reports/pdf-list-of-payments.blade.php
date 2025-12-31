<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Report Per College</title>
    <style>
        @page {
            margin-top: 15mm;
            margin-right: 20mm;
            margin-bottom: 15mm;
            margin-left: 15mm;
            size: A4 portrait;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            line-height: 1.4;
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
            font-size: 16px;
            margin-bottom: 5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin: 10px 0 8px 2%;
            width: 96%;
            margin-left: auto;
            margin-right: auto;
        }
        .info-section {
            margin-bottom: 15px;
            width: 96%;
            margin-left: auto;
            margin-right: auto;
            padding-left: 2%;
        }
        .info-section p {
            margin: 4px 0;
            font-size: 10px;
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
            padding: 6px 4px;
            text-align: left;
            font-size: 10px;
            line-height: 1.3;
            word-wrap: break-word;
            overflow: hidden;
        }
        th {
            background-color: #e8e8e8;
            font-weight: bold;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .indent {
            padding-left: 20px;
        }
        .total-row {
            font-weight: bold;
        }
        .description-header {
            background-color: #ffffff;
            font-weight: bold;
        }
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            padding-left: 2%;
            text-align: left;
            font-size: 10px;
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
            <h1>UNIVERSITY EVENT ATTENDANCE</h1>
            <h2>FINES MANAGEMENT FINANCIAL REPORT PER COLLEGE</h2>
        </div>

        <div class="section-title">I. General Information</div>
        <div class="info-section">
            <p>College: {{ $selectedCollege->college_name }}</p>
            <p>Date: {{ date('F d, Y') }}</p>
            <p>Semester: {{ $selectedSemester ? $selectedSemester->semester : 'N/A' }}</p>
            <p>Custodian of Funds: _______________________</p>
            <p>Report Period Covered: {{ $selectedSemester ? 'A.Y ' . $selectedSemester->school_year : 'N/A' }}</p>
        </div>

        <div class="section-title">III. Summary of Attendance Fines</div>
        
        <table>
            <thead>
                <tr>
                    <td colspan="4" class="description-header">Description</td>
                </tr>
                <tr>
                    <td colspan="4"><strong>Event Title:</strong> {{ $selectedEvent->event_name }}</td>
                </tr>
                <tr>
                    <td colspan="4">
                        <strong>Event Date:</strong> 
                        @if($selectedEvent->event_schedule_type == 'whole_day')
                            {{ \Carbon\Carbon::parse($selectedEvent->start_datetime_morning)->format('F d, Y') }}
                        @elseif($selectedEvent->event_schedule_type == 'half_day_morning')
                            {{ \Carbon\Carbon::parse($selectedEvent->start_datetime_morning)->format('F d, Y') }}
                        @elseif($selectedEvent->event_schedule_type == 'half_day_afternoon')
                            {{ \Carbon\Carbon::parse($selectedEvent->start_datetime_afternoon)->format('F d, Y') }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th style="width: 40%;">Program</th>
                    <th style="width: 20%;" class="text-center">No. of Participants</th>
                    <th style="width: 20%;" class="text-center">No. of Fined Participants</th>
                    <th style="width: 20%;" class="text-center">Total Fines Collected</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['programs'] as $program)
                <tr>
                    <td class="indent">• {{ $program['program_name'] }}</td>
                    <td class="text-center">{{ $program['participants'] }}</td>
                    <td class="text-center">{{ $program['fined_participants'] }}</td>
                    <td class="text-center">Php {{ number_format($program['total_fines'], 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3"><strong>Total Uncollected Fines</strong></td>
                    <td class="text-center"><strong>Php {{ number_format($reportData['total_uncollected'], 2) }}</strong></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3"><strong>Total Collected Fines</strong></td>
                    <td class="text-center"><strong>Php {{ number_format($reportData['total_collected'], 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 30px; width: 96%; margin-left: auto; margin-right: auto; font-size: 10px;">
            <div style="margin-bottom: 20px;">
                <p style="font-weight: bold; margin-bottom: 10px;">Certification</p>
                <p style="text-align: justify; line-height: 1.6;">
                    I hereby certify that the above information is true, accurate, and complete to the best of my knowledge and that all collected fines were handled in accordance with university policies.
                </p>
            </div>

            <div style="margin-top: 30px;">
                <p style="font-weight: bold; margin-bottom: 5px;">Prepared by:</p>
                <p>Name: ___________________________</p>
                <p>Signature: _______________________</p>
                <p>Date: ___________________________</p>
            </div>

            <div style="margin-top: 20px;">
                <p style="font-weight: bold; margin-bottom: 5px;">Reviewed by:</p>
                <p>Name: ___________________________</p>
                <p>Position: ________________________</p>
                <p>Signature: _______________________</p>
                <p>Date: ___________________________</p>
            </div>

            <div style="margin-top: 20px;">
                <p style="font-weight: bold; margin-bottom: 5px;">Approved by:</p>
                <p>Name: ___________________________</p>
                <p>Position: ________________________</p>
                <p>Signature: _______________________</p>
                <p>Date: ___________________________</p>
            </div>
        </div>
    </div>
</body>
</html>
