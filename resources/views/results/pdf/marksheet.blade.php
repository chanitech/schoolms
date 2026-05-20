@php
use App\Models\Grade;
use App\Models\Division;
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Results PDF</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background-color: #f0f0f0; }
        .header { text-align: center; margin-bottom: 20px; }
        .student-info { margin-bottom: 10px; }
        .page-break { page-break-after: always; }
        .best-subject { background-color: #d9ffd9; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $class->name }} Results</h2>
        <p>{{ $session->name }} | Exam: {{ $exam->name }}</p>
        @if($department)
            <p>Department: {{ $department->name }}</p>
        @endif
    </div>

    @foreach($studentsData as $row)
        <div class="student-info">
            <strong>Student:</strong> {{ $row['student']->name }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Mark</th>
                    <th>Grade</th>
                    <th>Point</th>
                </tr>
            </thead>
            <tbody>
                @php $i = 1; @endphp
                @foreach($row['subjectsData'] as $subject)
                    <tr @if($row['bestSubjects']->contains('subject_id', $subject['subject_id'])) class="best-subject" @endif>
                        <td>{{ $i++ }}</td>
                        <td>{{ $subject['name'] }}</td>
                        <td>{{ $subject['mark'] ?? '-' }}</td>
                        <td>{{ $subject['grade'] }}</td>
                        <td>{{ $subject['point'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p>
            <strong>Total Marks (Best 7):</strong> {{ $row['total_marks'] }} |
            <strong>Total Points:</strong> {{ $row['total_points'] }} |
            <strong>GPA:</strong> {{ $row['gpa'] }} |
            <strong>Division:</strong> {{ $row['division'] }}
        </p>

        <div class="page-break"></div>
    @endforeach
</body>
</html>
