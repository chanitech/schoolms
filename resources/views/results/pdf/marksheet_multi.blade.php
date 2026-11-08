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
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
        }

        td.subject-name { text-align: left; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -14mm;
            left: 0;
            right: 0;
            height: 16px;
            font-size: 9px;
            color: #555;
            border-top: 0.5px solid #999;
            padding: 5px 10px;
            text-align: right;
        }

        .dense th, .dense td { padding: 1.5px 3px; font-size: 9px; }
        .dense-card .sign-comment { min-height: 0; margin-bottom: 2px; }
        .dense-card .sign-space { height: 28px; }
        .dense-card .sign-table, .dense-card .parent-line { margin-top: 5px; }
        .report-title { margin: 6px 0 0; font-size: 13px; font-weight: bold; letter-spacing: 1px; }
        .provisional { margin: 4px 0; padding: 3px; border: 1px solid #b00; color: #b00; text-align: center; font-weight: bold; font-size: 10px; }
        .sign-table { width: 100%; margin-top: 8px; border-collapse: collapse; }
        .sign-table td { width: 50%; border: 1px solid #333; padding: 6px 8px; vertical-align: top; text-align: left; }
        .sign-label { font-weight: bold; font-size: 10.5px; }
        .sign-comment { margin: 3px 0 6px; font-size: 10.5px; min-height: 28px; }
        .sign-space { height: 34px; }
        .sign-space img { max-height: 34px; max-width: 150px; }
        .sign-line { border-top: 1px solid #333; font-size: 9.5px; padding-top: 2px; }
        .parent-line { margin-top: 8px; font-size: 10.5px; }
        .verify-line { margin-top: 6px; font-size: 9px; color: #333; }
        .verify-code { font-family: DejaVu Sans Mono, monospace; font-weight: bold; }

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

{{-- Watermark + logos — always the CURRENT school's own branding (from
     School Info settings), never another school's. No hardcoded fallbacks. --}}
@php
    $schoolLogoB64 = null;
    if (!empty($school?->logo)) {
        $logoPath = storage_path('app/public/' . $school->logo);
        if (file_exists($logoPath)) {
            $ext  = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $mime = in_array($ext, ['jpg', 'jpeg']) ? 'image/jpeg' : 'image/' . $ext;
            $schoolLogoB64 = base64_encode(file_get_contents($logoPath));
            $logoL = "data:{$mime};base64,{$schoolLogoB64}";
        }
    }
    $logoL ??= null;

    $logoRPath = public_path('vendor/adminlte/dist/img/shulepro-icon.png');
    $logoR     = file_exists($logoRPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoRPath)) : null;
@endphp
@if($logoL)
<div class="watermark">
    <img src="{{ $logoL }}" alt="Watermark">
</div>
@endif

<div class="footer">
    Generated by: {{ $school->name ?? config('app.name') }} | {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}
    &nbsp;|&nbsp; Powered by ShulePRO — a Chani Technologies product · +255 713 209 535 · www.chanitech.co.tz
</div>

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
    $hasExtraSubjects = $row['overallSubjects']->count() > $row['bestSubjectIds']->count();
@endphp

{{-- Header --}}
<div class="header">
    @if($logoL)<img src="{{ $logoL }}" class="logo-left" alt="Logo">@endif
    @if($logoR)<img src="{{ $logoR }}" class="logo-right" alt="Logo">@endif

    <div class="school-details">
        <h1>{{ $school->name ?? config('app.name') }}</h1>
        @if($school?->motto)<h2>{{ $school->motto }}</h2>@endif
        <div>{{ collect([$school->address ?? null, $school->phone ?? null, $school->email ?? null, $school->website ?? null])->filter()->implode(' | ') }}</div>
    </div>

    <div class="report-title">STUDENT PROGRESS REPORT</div>
    <h3 class="class-session">{{ $class->name ?? '' }} - {{ $session->name ?? '' }} - {{ $selectedExam->name }}</h3>
    @if($department)
        <div>Department: {{ $department->name }}</div>
    @endif
</div>

@unless($isOfficial)
    <div class="provisional">PROVISIONAL: these results have not been published and this report is not signed.</div>
@endunless

{{-- Student Info with Photo --}}
<div class="student-header">
    @if($photoB64)
        <img src="{{ $photoB64 }}" class="student-photo" alt="Student Photo">
    @endif
    <div>
        Student: {{ $studentName }} (ADM: {{ $student->admission_no ?? 'N/A' }})
        @if($isIncomplete)
            <span style="color:red; font-size:10px;"> &nbsp; (Incomplete)</span>
        @endif
    </div>
</div>

{{-- Results Table --}}
<div class="{{ $subjects->count() > 11 ? 'dense-card' : '' }}">
<table class="{{ $subjects->count() > 11 ? 'dense' : '' }}">
    <thead>
        <tr>
            <th rowspan="2">#</th>
            <th rowspan="2">Subject</th>
            @foreach($exams as $exam)
                <th colspan="3">{{ $exam->name }}</th>
            @endforeach
            @if($exams->count() > 1)
                <th colspan="3">Overall</th>
            @endif
            <th rowspan="2">Remark</th>
            <th rowspan="2">Teacher</th>
        </tr>
        <tr>
            @foreach($exams as $exam)
                <th>Mark</th>
                <th>Grade</th>
                <th>Point</th>
            @endforeach
            @if($exams->count() > 1)
                <th>Average</th>
                <th>Grade</th>
                <th>Point</th>
            @endif
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
                $overallSub = $row['overallSubjects']->get($subject->id);
            @endphp
            <tr>
                <td>{{ $i++ }}</td>
                <td class="subject-name">{{ $subject->name }}{{ $hasExtraSubjects && $row['bestSubjectIds']->contains($subject->id) ? ' *' : '' }}</td>
                @foreach($exams as $exam)
                    @php
                        $examData = $row['exams'][$exam->id];
                        $sub = $examData['subjectsData']->firstWhere('subject_id', $subject->id);
                        $isBest = $examData['bestSubjects']->contains('subject_id', $subject->id);
                    @endphp
                    <td>{{ $sub['mark'] ?? '-' }}</td>
                    <td>{{ $sub['grade'] ?? '-' }}</td>
                    <td>{{ isset($sub['mark']) ? $sub['point'] : '-' }}</td>
                @endforeach
                @if($exams->count() > 1)
                    <td><strong>{{ $overallSub['mark'] ?? '-' }}</strong></td>
                    <td><strong>{{ $overallSub['grade'] ?? '-' }}</strong></td>
                    <td><strong>{{ $overallSub['point'] ?? '-' }}</strong></td>
                @endif
                <td>{{ $overallSub['remark'] ?? '' }}</td>
                <td>{{ $subjectTeachers[$subject->id] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@if($row['attendance'])
    <div class="summary-box" style="margin-bottom:4px;">
        <strong>Attendance</strong> ({{ $session->name }} to {{ $attendanceUpTo->format('d M Y') }}):
        <strong>{{ $row['attendance']['rate'] }}%</strong> &nbsp; | &nbsp;
        Attended {{ $row['attendance']['attended'] }} of {{ $row['attendance']['days'] }} school days &nbsp; | &nbsp;
        Absent {{ $row['attendance']['absent'] }} &nbsp; | &nbsp;
        Late {{ $row['attendance']['late'] }}
        @if($row['attendance']['excused']) &nbsp; | &nbsp; Excused {{ $row['attendance']['excused'] }} @endif
    </div>
@endif

{{-- Summary --}}
<div class="summary-box">
    <strong>{{ $exams->count() > 1 ? 'Overall Average' : 'Average' }} (Best 7):</strong> {{ $displayAvg }} &nbsp; | &nbsp;
    <strong>Grade:</strong> {{ $row['overallGrade']->name ?? '-' }}{{ !empty($row['overallGrade']?->description) ? ' (' . $row['overallGrade']->description . ')' : '' }} &nbsp; | &nbsp;
    <strong>Total Points:</strong> {{ $displayPoints }} &nbsp; | &nbsp;
    <strong>GPA:</strong> {{ $displayGpa }} &nbsp; | &nbsp;
    <strong>Division:</strong> {{ $displayDiv }} &nbsp; | &nbsp;
    <strong>Position:</strong> {{ $displayPos }}{{ is_numeric($displayPos) ? ' of ' . $classSize : '' }}
</div>

{{-- Dynamic Grade Legend --}}
@if(isset($grades) && $grades->isNotEmpty())
<div class="grade-legend">
    <strong>Grade Scale:</strong>
    @foreach($grades as $g)
        <span class="grade-badge">
            <strong>{{ $g->name }}</strong> = {{ $g->min_mark }}–{{ $g->max_mark }}{{ $g->description ? ' ' . $g->description : '' }}
        </span>
    @endforeach
    @if($hasExtraSubjects)
        <span style="margin-left:10px; font-size:9px; color:#555;">* Counted in the best 7 used for the result. All subjects taken are listed.</span>
    @endif
</div>
@endif

@php
    $approvedOn = $isOfficial ? $selectedExam->published_at?->format('d M Y') : null;
    $signers = [
        ["Class Coordinator's Comment", $row['comments']['coordinator'], $coordinatorSignature, $coordinator ? trim($coordinator->first_name . ' ' . $coordinator->last_name) : '', 'Class Coordinator'],
        ["Head of School's Comment", $row['comments']['head'], $headSignature, $head->name ?? '', 'Head of School'],
    ];
@endphp
<table class="sign-table">
    <tr>
        @foreach($signers as [$label, $comment, $signature, $name, $role])
            <td>
                <div class="sign-label">{{ $label }}</div>
                <div class="sign-comment">{{ $comment }}</div>
                <div class="sign-space">@if($signature)<img src="{{ $signature }}" alt="Signature">@endif</div>
                <div class="sign-line">{{ $name }}{{ $name ? ', ' : '' }}{{ $role }}{{ $approvedOn ? ' · ' . $approvedOn : '' }}</div>
            </td>
        @endforeach
    </tr>
</table>

<div class="parent-line">Parent / Guardian signature: ______________________________ &nbsp;&nbsp; Date: ________________</div>

@if($row['signature'])
    <div class="verify-line">
        Digitally verified. Code <span class="verify-code">{{ $row['signature']->code }}</span>.
        Confirm this report is genuine at {{ route('verify.document') }}
    </div>
@endif
</div>


@unless($loop->last)
<div class="page-break"></div>
@endunless
@endforeach

</body>
</html>