<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Results</title>
    <style>
        @page {
            margin: 20px 10px;
            header: page-header;
            footer: page-footer;
        }
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.2; }
        table { width: 100%; border-collapse: collapse; page-break-inside: auto; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background-color: #f0f0f0; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        .header { text-align: center; margin-bottom: 10px; }
        .school-logo { height: 80px; }
        h1, h2, h3 { margin: 0; }
        .watermark {
            position: fixed;
            top: 45%;
            left: 25%;
            width: 50%;
            text-align: center;
            font-size: 100px;
            color: rgba(200, 200, 200, 0.2);
            transform: rotate(-45deg);
            z-index: -1000;
        }
        .page-header { text-align: center; font-size: 14px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="watermark">CONFIDENTIAL</div>

    <div class="header">
        @if($schoolInfo->logo)
            <img src="{{ public_path('uploads/'.$schoolInfo->logo) }}" class="school-logo" alt="Logo">
        @endif
        <h1>{{ $schoolInfo->name ?? 'School Name' }}</h1>
        <h3>{{ $schoolInfo->address ?? '' }} | {{ $schoolInfo->phone ?? '' }}</h3>
        <h2>{{ $class->name ?? '' }} - {{ $exam->name ?? '' }} Results</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Position</th>
                <th>Student Name</th>
                @foreach($subjects as $subject)
                    <th>{{ $subject->name }}</th>
                @endforeach
                <th>Total Points</th>
                <th>GPA</th>
                <th>Division</th>
            </tr>
        </thead>
        <tbody>
            @foreach($studentsData as $data)
                <tr>
                    <td>{{ $data['position'] }}</td>
                    <td>{{ $data['student']->full_name }}</td>
                    @foreach($subjects as $subject)
                        <td>{{ $data['subjectsData'][$subject->id]['mark'] ?? '-' }}</td>
                    @endforeach
                    <td>{{ $data['totalPoints'] }}</td>
                    <td>{{ $data['gpa'] }}</td>
                    <td>{{ $data['division'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
