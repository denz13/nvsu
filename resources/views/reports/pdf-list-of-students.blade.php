<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Students</title>
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
            <h1>LIST OF STUDENTS</h1>
            <p>Generated on: {{ date('F d, Y h:i A') }}</p>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 7%;">ID Number</th>
                        <th style="width: 15%;">Student Name</th>
                        <th style="width: 12%;">Address</th>
                        <th style="width: 12%;">College</th>
                        <th style="width: 24%;">Program</th>
                        <th style="width: 8%;">Year Level</th>
                        <th style="width: 22%;">Organization</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td>{{ $student->id_number }}</td>
                        <td>{{ $student->student_name }}</td>
                        <td>{{ $student->address ?? 'N/A' }}</td>
                        <td>{{ $student->college ? $student->college->college_name : 'N/A' }}</td>
                        <td>{{ $student->program ? $student->program->program_name : 'N/A' }}</td>
                        <td class="text-center">{{ $student->year_level }}</td>
                        <td>{{ $student->organization ? $student->organization->organization_name : 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 20px;">No students found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>Total Students: {{ $students->count() }}</p>
        </div>
    </div>
</body>
</html>

