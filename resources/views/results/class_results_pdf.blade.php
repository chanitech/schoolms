<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Results - {{ $class->name }} - {{ $exam->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        th { background-color: #f2f2f2; }
        .text-left { text-align: left; }
        .header { text-align: center; margin-bottom: 20px; }
        .header img { width: 80px; }
        .header h1, .header h3 { margin: 2px; }
    </style>
</head>
<body>

<div class="header">
    @if(isset($logo_img))
        <img src="{{ $logo_img }}" alt="School Logo">
    @endif
    <h1>{{ $class->school->name ?? 'School Name' }}</h1>
    <h3>Class Results: {{ $class->name }} - Exam: {{ $exam->name }}</h3>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Student</th>
            @foreach($subjects as $subject)
                <th>{{ $subject['name'] }}</th>
            @endforeach
            <th>Total Marks (Best 7)</th>
            <th>Average (Best 7)</th>
            <th>Division</th>
            <th>Total Points (Best 7)</th>
            <th>GPA</th>
            <th>Position</th>
        </tr>
    </thead>
    <tbody>
        @foreach($studentsData as $i => $student)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="text-left">{{ $student['student']->first_name }} {{ $student['student']->last_name }}</td>
                @foreach($student['subjectsData'] as $sub)
                    <td>{{ $sub['mark'] }} ({{ $sub['grade'] }})</td>
                @endforeach
                <td>{{ $student['totalMarks'] }}</td>
                <td>{{ $student['average'] }}</td>
                <td>{{ $student['division'] }}</td>
                <td>{{ $student['totalPoints'] }}</td>
                <td>{{ number_format($student['gpa'], 2) }}</td>
                <td>{{ $student['position'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
