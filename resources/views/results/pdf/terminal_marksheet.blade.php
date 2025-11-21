<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Terminal Marksheet</title>
    <style>
        @page { size: A4 landscape; margin: 15mm; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .page { page-break-after: always; position: relative; }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 30%;
            left: 25%;
            width: 300px;
            opacity: 0.08;
            z-index: 0;
            pointer-events: none;
        }

        /* Header */
        .header { text-align: center; margin-bottom: 10px; z-index: 1; position: relative; }
        .header img { height: 60px; margin-bottom: 4px; }
        .header h2 { margin: 0; font-size: 18px; }
        .header h4 { margin: 2px 0; font-size: 13px; }

        /* Tables */
        .student-info, .subject-table, .summary, .comments { width: 100%; margin-bottom: 8px; z-index: 1; position: relative; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #555; padding: 4px 5px; text-align: center; }
        th { background: #f0f0f0; }
        .best { background: #e8f4ff; font-weight: bold; }
        .fail { color: red; font-weight: bold; }
        .summary td { padding: 5px; font-weight: bold; text-align: left; }

        /* Footer comments & stamp */
        .comments td { padding: 5px; vertical-align: top; }
        .signature-line { border-bottom: 1px solid #000; width: 200px; display: inline-block; margin-top: 20px; }
        .stamp { text-align: center; margin-top: 10px; }
        .stamp img { width: 120px; opacity: 0.5; }
    </style>
</head>
<body>

@foreach($studentsData as $data)
<div class="page">

    {{-- Watermark --}}
    <div class="watermark">
        <img src="{{ public_path($school->logo ?? 'vendor/adminlte/dist/img/MEMA.webp') }}" alt="School Logo">
    </div>

    {{-- Header --}}
    <div class="header">
        <h2>{{ $school->name ?? 'SCHOOL NAME' }}</h2>
        <h4>{{ $school->address ?? '' }} | {{ $school->phone ?? '' }}</h4>
        <h4>Terminal Report – {{ $academicSession->name ?? '' }}</h4>
    </div>

    {{-- Student Info --}}
    <table class="student-info">
        <tr>
            <td><strong>Name:</strong> {{ $data['student']->full_name ?? '-' }}</td>
            <td><strong>Admission No:</strong> {{ $data['student']->admission_no ?? '-' }}</td>
            <td><strong>Class:</strong> {{ $data['student']->schoolClass->name ?? '-' }}</td>
            <td><strong>Exams:</strong>
                @if(!empty($data['exams']))
                    @foreach($data['exams'] as $exam)
                        {{ $exam->name ?? '-' }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Department:</strong> {{ $data['student']->department->name ?? '-' }}</td>
            <td><strong>Session:</strong> {{ $academicSession->name ?? '-' }}</td>
            <td colspan="2">&nbsp;</td>
        </tr>
    </table>

    {{-- Subjects Table --}}
    <table class="subject-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Subject</th>
                @if(!empty($data['exams']))
                    @foreach($data['exams'] as $exam)
                        <th>{{ $exam->name ?? '-' }}</th>
                    @endforeach
                @endif
                <th>Total</th>
                <th>Average</th>
                <th>Grade</th>
                <th>Point</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($data['subjectsData']))
                @foreach($data['subjectsData'] as $i => $subject)
                    @php
                        $marks = $subject['marks'] ?? [];
                        $total = array_sum(array_map(fn($m) => $m ?? 0, $marks));
                        $avg = count($marks) > 0 ? $total / count($marks) : 0;
                        $remark = $subject['grade_obj']->description ?? ($avg >= 1 ? 'Pass' : 'Fail');
                        $isBest = !empty($data['bestSubjects']) 
                                  ? collect($data['bestSubjects'])->contains(fn($s) => $s['subject_id'] == ($subject['subject_id'] ?? 0)) 
                                  : false;
                    @endphp
                    <tr class="{{ $isBest ? 'best' : '' }} {{ $remark === 'Fail' ? 'fail' : '' }}">
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $subject['name'] ?? '-' }}</td>
                        @foreach($marks as $mark)
                            <td>{{ $mark !== null ? number_format($mark,2) : '-' }}</td>
                        @endforeach
                        <td>{{ number_format($total,2) }}</td>
                        <td>{{ number_format($avg,2) }}</td>
                        <td>{{ $subject['grade'] ?? '-' }}</td>
                        <td>{{ $subject['point'] ?? '-' }}</td>
                        <td>{{ $remark }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="{{ 4 + (count($data['exams'] ?? [])) }}">No subjects available</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- Summary --}}
    <table class="summary">
        <tr>
            <td>Total Points: {{ $data['total_points'] ?? '-' }}</td>
            <td>GPA: {{ number_format($data['gpa'] ?? 0, 2) }}</td>
            <td>Division: {{ $data['division'] ?? '-' }}</td>
            <td>Position: {{ $data['position'] ?? '-' }}</td>
        </tr>
    </table>

    {{-- Comments & Signatures --}}
    <table class="comments">
        <tr>
            <td><strong>Class Coordinator Comment:</strong><br>{{ $data['class_coordinator_comment'] ?? '-' }}</td>
            <td><strong>Head of Department Comment:</strong><br>{{ $data['hod_comment'] ?? '-' }}</td>
        </tr>
        <tr>
            <td><div class="signature-line"></div><br>Class Coordinator</td>
            <td><div class="signature-line"></div><br>Head of Department</td>
        </tr>
        <tr>
            <td colspan="2" class="stamp">
                <img src="{{ public_path($school->logo ?? 'vendor/adminlte/dist/img/MEMA.webp') }}" alt="School Stamp">
            </td>
        </tr>
    </table>

</div>
@endforeach

</body>
</html>
