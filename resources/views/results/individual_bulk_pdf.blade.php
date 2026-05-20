<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Marksheets</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .page-break { page-break-after: always; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background: #f0f0f0; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
@foreach($students as $student)
    <div class="header">
        <h2>{{ $student->full_name }} - Marksheet</h2>
        <p>Admission No: {{ $student->admission_no }}</p>
        <p>Class: {{ $student->schoolClass->name ?? '-' }} | Session: {{ $student->academicSession->name ?? '-' }} | Department: {{ $student->department->name ?? '-' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Marks</th>
                <th>Grade</th>
                <th>Points</th>
            </tr>
        </thead>
        <tbody>
            @foreach($student->marks as $index => $mark)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $mark->subject->name ?? '-' }}</td>
                    <td>{{ $mark->marks_obtained }}</td>
                    <td>{{ $mark->grade->name ?? '-' }}</td>
                    <td>{{ $mark->grade->points ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div>
        <strong>Total Marks:</strong> {{ $student->marks->sum('marks_obtained') }}<br>
        <strong>GPA:</strong> {{ $student->marks->avg('grade.points') ?? '-' }}
    </div>

    <div class="page-break"></div>
@endforeach
</body>
</html>
