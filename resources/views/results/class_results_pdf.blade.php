<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Results</title>
    <style>
        @page { margin: 20px 10px; }
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.2; }
        table { width: 100%; border-collapse: collapse; page-break-inside: auto; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        th { background-color: #f0f0f0; font-weight: bold; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        .header { text-align: center; margin-bottom: 10px; }
        .school-logo { height: 80px; }
        h1, h2, h3 { margin: 2px; }
        .watermark {
            position: fixed;
            top: 40%;
            left: 25%;
            width: 50%;
            text-align: center;
            font-size: 100px;
            color: rgba(200, 200, 200, 0.2);
            transform: rotate(-45deg);
            z-index: -1000;
        }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="watermark">CONFIDENTIAL</div>

    <div class="header">
        @if($school->logo)
            <img src="{{ public_path('uploads/'.$school->logo) }}" class="school-logo" alt="Logo">
        @endif
        <h1>{{ $school->name ?? 'School Name' }}</h1>
        <h3>{{ $school->address ?? '' }} | {{ $school->phone ?? '' }}</h3>
        <h2>{{ $class->name ?? '' }} - {{ $exam->name ?? '' }} Results</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                @foreach($subjects as $subject)
                    <th>{{ $subject->name }}</th>
                @endforeach
                <th>Total Points</th>
                <th>GPA</th>
                <th>Division</th>
                <th>Position</th>
            </tr>
        </thead>
        <tbody>
            @foreach($studentsData as $data)
                <tr>
                    <td>{{ $data['student']->full_name }}</td>
                    @foreach($subjects as $subject)
                        @php
                            $sub = $data['subjectsData'][$subject->id] ?? null;
                        @endphp
                        <td>
                            @if($sub)
                                {{ $sub['mark'] }} ({{ $sub['grade'] }})
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                    <td>{{ $data['total_points'] }}</td>
                    <td>{{ $data['gpa'] }}</td>
                    <td>{{ $data['division'] }}</td>
                    <td>{{ $data['position'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
