<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Marksheet</title>
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
        <img src="{{ public_path($schoolInfo['logo_img'] ?? 'vendor/adminlte/dist/img/MEMA.webp') }}" alt="School Logo">
    </div>

    

    {{-- Header --}}
    <div class="header">
        @if($school->logo)
            <img src="{{ public_path($schoolInfo['logo_img'] ?? 'vendor/adminlte/dist/img/MEMA.webp') }}" alt="School Logo">
        @endif
        <h2>{{ $school->name ?? 'SCHOOL NAME' }}</h2>
        <h4>{{ $school->address ?? '' }} | {{ $school->phone ?? '' }}</h4>
        <h4>Student Marksheet – {{ $academicSession->name ?? '' }}</h4>
    </div>

    {{-- Student Info --}}
    <table class="student-info">
        <tr>
            <td><strong>Name:</strong> {{ $data['student']->full_name }}</td>
            <td><strong>Admission No:</strong> {{ $data['student']->admission_no }}</td>
            <td><strong>Class:</strong> {{ $data['student']->schoolClass->name ?? '-' }}</td>
            <td><strong>Exam:</strong> {{ $exam->name ?? '-' }}</td>
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
                <th>Mark</th>
                <th>Grade</th>
                <th>Point</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['subjectsData'] as $i => $subject)
            @php
                $isBest = $data['bestSubjects']->contains(fn($s) => $s['subject_id'] == $subject['subject_id']);
                $remark = $subject['grade_obj']->description ?? ($subject['point'] >= 1 ? 'Pass' : 'Fail');
            @endphp
            <tr class="{{ $isBest ? 'best' : '' }}">
                <td>{{ $i + 1 }}</td>
                <td>{{ $subject['name'] }}</td>
                <td class="{{ $remark === 'Fail' ? 'fail' : '' }}">{{ $subject['mark'] }}</td>
                <td>{{ $subject['grade'] }}</td>
                <td>{{ $subject['point'] }}</td>
                <td class="{{ $remark === 'Fail' ? 'fail' : '' }}">{{ $remark }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Summary --}}
    <table class="summary">
        <tr>
            <td>Total Points: {{ $data['total_points'] }}</td>
            <td>GPA: {{ $data['gpa'] }}</td>
            <td>Division: {{ $data['division'] }}</td>
            <td>Position: {{ $data['position'] }}</td>
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
                <img src="{{ public_path($schoolInfo['logo_img'] ?? 'vendor/adminlte/dist/img/MEMA.webp') }}" alt="School Stamp">
            </td>
        </tr>
    </table>

</div>
@endforeach

</body>
</html>
