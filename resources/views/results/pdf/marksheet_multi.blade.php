<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $school->name ?? 'School' }} – Student Progress Report</title>
    <style>
        @page {
            margin: 12mm 10mm 20mm 10mm;   /* printer‑safe margins */
            size: A4 landscape;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10.5px;
            color: #000;
            position: relative;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.08;
        }
        .watermark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 12px;
            position: relative;
        }

        .header img.logo-left {
            position: absolute;
            top: 0;
            left: 0;
            width: 60px;
            height: auto;
        }
        .header img.logo-right {
            position: absolute;
            top: 0;
            right: 0;
            width: 60px;
            height: auto;
        }
        .school-details {
            text-align: center;
            margin-top: 0;
        }
        .school-details h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .school-details h2 {
            margin: 0;
            font-size: 14px;
            font-style: italic;
            font-weight: normal;
        }
        .school-details div {
            font-size: 11px;
        }
        h3.class-session {
            margin: 5px 0 0 0;
            font-size: 13px;
            font-weight: bold;
        }

        /* Student info with photo */
        .student-header {
            margin: 10px 0 6px 0;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .student-photo {
            width: 90px;
            height: 98px;
            border: 2px solid #00643c;
            object-fit: cover;
            flex-shrink: 0;
        }
        .photo-placeholder {
            width: 90px;
            height: 98px;
            border: 2px solid #00643c;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #aaa;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 0.7px solid #333;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
            font-weight: bold;
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
        }

        td.subject-name { text-align: left; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            font-size: 10px;
            color: #555;
            border-top: 0.5px solid #999;
            padding: 5px 10px;
            text-align: right;
        }

        .summary-box {
            margin-top: 5px;
            padding: 6px 8px;
            border: 1px solid #333;
            background: #f8f8f8;
            font-size: 11px;
        }

        .grade-legend {
            margin-top: 5px;
            font-size: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .grade-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-weight: bold;
        }

        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 4px;
            font-size: 10px;
        }

        .page-break { page-break-after: always; }
    </style>
</head>
<body>

{{-- Watermark --}}
@php
    $wmPath    = public_path('vendor/adminlte/dist/img/MEMA.png');
    $wmB64     = file_exists($wmPath) ? base64_encode(file_get_contents($wmPath)) : null;

    // Dynamic left logo from school info, fallback to MEMA.png
    $logoLPath = $school->logo_left ?? null;
    if ($logoLPath && file_exists(public_path($logoLPath))) {
        $logoL = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path($logoLPath)));
    } else {
        $logoL = file_exists(public_path('vendor/adminlte/dist/img/MEMA.png'))
            ? 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('vendor/adminlte/dist/img/MEMA.png')))
            : null;
    }
    $logoRPath = public_path('vendor/adminlte/dist/img/schoolms-icon.png');
    $logoR     = file_exists($logoRPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoRPath)) : null;
@endphp
@if($wmB64)
<div class="watermark">
    <img src="data:image/png;base64,{{ $wmB64 }}" alt="Watermark">
</div>
@endif

@foreach($studentsData as $row)
@php
    $student     = $row['student'];
    $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
    if (!$studentName) $studentName = $student->name ?? 'N/A';

    // Student photo base64
    $photoB64 = null;
    if (!empty($student->photo)) {
        $photoPath = storage_path('app/public/' . $student->photo);
        if (file_exists($photoPath)) {
            $ext      = strtolower(pathinfo($photoPath, PATHINFO_EXTENSION));
            $mime     = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg' : "image/$ext";
            $photoB64 = "data:$mime;base64," . base64_encode(file_get_contents($photoPath));
        }
    }

    // Summary display values (from controller)
    $isIncomplete  = !($row['eligible_for_rank'] ?? true);
    $displayPoints = $isIncomplete ? '-' : ($row['total_points'] ?? '-');
    $displayGpa    = $isIncomplete ? '-' : number_format($row['gpa'] ?? 0, 2);
    $displayDiv    = $row['division'] ?? '-';
    $displayPos    = $row['position'] ?? '-';
    $displayAvg    = number_format($row['average_mark'] ?? 0, 2);
@endphp

{{-- Header --}}
<div class="header">
    @if($logoL)<img src="{{ $logoL }}" class="logo-left" alt="Logo">@endif
    @if($logoR)<img src="{{ $logoR }}" class="logo-right" alt="Logo">@endif

    <div class="school-details">
        <h1>{{ $school->name ?? 'MEMA ASEP Learning Centre' }}</h1>
        <h2>{{ $school->motto ?? 'Motto: Maadili, Elimu, Maendeleo, Amani' }}</h2>
        <div>
            {{ $school->address ?? 'Kisarawe, Pwani' }} |
            {{ $school->phone ?? '+255' }} |
            {{ $school->email ?? 'info@mema.or.tz' }} |
            {{ $school->website ?? 'www.mema.ac.tz' }}
        </div>
    </div>

    <h3 class="class-session">{{ $class->name ?? '' }} - {{ $session->name ?? '' }} Results Report</h3>
    @if($department)
        <div>Department: {{ $department->name }}</div>
    @endif
</div>

{{-- Student Info with Photo --}}
<div class="student-header">
    @if($photoB64)
        <img src="{{ $photoB64 }}" class="student-photo" alt="Student Photo">
    @else
        <div class="photo-placeholder">&#128100;</div>
    @endif
    <div>
        Student: {{ $studentName }} (ADM: {{ $student->admission_no ?? 'N/A' }})
        @if($isIncomplete)
            <span style="color:red; font-size:10px;"> &nbsp; (Incomplete)</span>
        @endif
    </div>
</div>

{{-- Results Table --}}
<table>
    <thead>
        <tr>
            <th rowspan="2">#</th>
            <th rowspan="2">Subject</th>
            @foreach($exams as $exam)
                <th colspan="3">{{ $exam->name }}</th>
            @endforeach
            <th rowspan="2">Total<br>Marks</th>
            <th rowspan="2">Average<br>Points</th>
            <th rowspan="2">GPA</th>
        </tr>
        <tr>
            @foreach($exams as $exam)
                <th>Mark</th>
                <th>Grade</th>
                <th>Point</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php $i = 1; @endphp
        @foreach($subjects as $subject)
            @php
                $highlight = false;
                foreach($exams as $exam){
                    if($row['exams'][$exam->id]['bestSubjects']->contains('subject_id', $subject->id)){
                        $highlight = true;
                    }
                }
                $subjectTotalMarks = 0;
                $subjectTotalPoints = 0;
                $countExamsWithMarks = 0;
            @endphp
            <tr>
                <td>{{ $i++ }}</td>
                <td class="subject-name">{{ $subject->name }}</td>
                @foreach($exams as $exam)
                    @php
                        $examData = $row['exams'][$exam->id];
                        $sub = $examData['subjectsData']->firstWhere('subject_id', $subject->id);
                        $isBest = $examData['bestSubjects']->contains('subject_id', $subject->id);
                    @endphp
                    <td>{{ $sub['mark'] ?? '-' }}</td>
                    <td>{{ $sub['grade'] ?? '-' }}</td>
                    <td>{{ $sub['point'] ?? 0 }}</td>
                    @php
                        if(isset($sub['mark'])){
                            $subjectTotalMarks += $sub['mark'];
                            $subjectTotalPoints += $sub['point'];
                            $countExamsWithMarks++;
                        }
                    @endphp
                @endforeach
                <td>{{ $subjectTotalMarks }}</td>
                <td>{{ $countExamsWithMarks ? round($subjectTotalPoints / $countExamsWithMarks, 2) : 0 }}</td>
                <td>{{ $countExamsWithMarks ? round($subjectTotalPoints / $countExamsWithMarks, 2) : 0 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- Summary --}}
<div class="summary-box">
    <strong>Total Marks (Best 7):</strong> {{ $row['total_marks'] }} &nbsp; | &nbsp;
    <strong>Total Points:</strong> {{ $displayPoints }} &nbsp; | &nbsp;
    <strong>GPA:</strong> {{ $displayGpa }} &nbsp; | &nbsp;
    <strong>Division:</strong> {{ $displayDiv }} &nbsp; | &nbsp;
    <strong>Position:</strong> {{ $displayPos }}
</div>

{{-- Dynamic Grade Legend --}}
@if(isset($grades) && $grades->isNotEmpty())
<div class="grade-legend">
    <strong>Grade Scale:</strong>
    @foreach($grades as $g)
        <span class="grade-badge">
            <strong>{{ $g->name }}</strong> = {{ $g->min_mark }}–{{ $g->max_mark }}
        </span>
    @endforeach
    <span style="margin-left:10px; font-size:9px; color:#555;">* Best 7 subjects used</span>
</div>
@endif


<div class="footer">
    Generated by: {{ $school->name ?? 'MEMA ASEP System' }} | {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}
</div>

<div class="page-break"></div>
@endforeach

</body>
</html>