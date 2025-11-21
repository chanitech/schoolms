<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Results PDF</title>
    <style>
        @page { margin: 20px; }
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

        .header .school-details {
            text-align: center;
            margin-top: 0;
        }

        .header .school-details h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .header .school-details h2 {
            margin: 0;
            font-size: 14px;
            font-style: italic;
            font-weight: normal;
        }

        .header .school-details div {
            font-size: 11px;
        }

        .header h3.class-session {
            margin: 5px 0 0 0;
            font-size: 13px;
            font-weight: bold;
        }

        /* Student info */
        .student-header {
            margin: 10px 0 6px 0;
            font-size: 12px;
            font-weight: bold;
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

        .best {
            background: #dff0d8;
            font-weight: bold;
        }



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

        .comments {
            margin-top: 15px;
            font-size: 11px;
        }

        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 30%;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 4px;
            font-size: 10px;
        }

        td.subject-name { text-align: left; }
        .page-break { page-break-after: always; }

    </style>
</head>
<body>

<!-- Watermark -->
<div class="watermark">
    @php
        $watermarkPath = public_path('vendor/adminlte/dist/img/MEMA.png');
        $watermark = base64_encode(file_get_contents($watermarkPath));
    @endphp
    <img src="data:image/png;base64,{{ $watermark }}" alt="Watermark">
</div>

@foreach($studentsData as $row)
<div class="header">
    @php
        $logoLeftPath = public_path('vendor/adminlte/dist/img/MEMA.png');
        $logoRightPath = public_path('vendor/adminlte/dist/img/MEMA.webp');

        $logoLeft = base64_encode(file_get_contents($logoLeftPath));
        $logoRight = base64_encode(file_get_contents($logoRightPath));
    @endphp

    <img src="data:image/png;base64,{{ $logoLeft }}" class="logo-left" alt="School Logo">
    <img src="data:image/webp;base64,{{ $logoRight }}" class="logo-right" alt="School Logo">

    <div class="school-details">
        <h1>MEMA ASEP Learning Centre</h1>
        <h2>Motto: Knowledge, Discipline, Excellence</h2>
        <div>Address: Kisarawe, Pwani | Phone: +255 342 546 | Email: info@mema.ac.tz | Website: www.mema.ac.tz</div>
    </div>

    <h3 class="class-session">{{ $class->name }} - {{ $session->name }} Results Report</h3>
    @if($department)
        <div>Department: {{ $department->name }}</div>
    @endif
</div>

<div class="student-header">
    Student: {{ $row['student']->name }} (ADM: {{ $row['student']->admission_no ?? 'N/A' }})
</div>

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

            <tr class="{{ $highlight ? 'best' : '' }}">
                <td>{{ $i++ }}</td>
                <td class="subject-name">{{ $subject->name }}</td>

                @foreach($exams as $exam)
                    @php
                        $examData = $row['exams'][$exam->id];
                        $sub = $examData['subjectsData']->firstWhere('subject_id', $subject->id);
                        $isBest = $examData['bestSubjects']->contains('subject_id', $subject->id);
                    @endphp

                    <td>{{ $sub['mark'] ?? '-' }} @if($isBest)*@endif</td>
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

                <td>{{ $subjectTotalMarks }} @if($highlight)*@endif</td>
                <td>{{ $countExamsWithMarks ? round($subjectTotalPoints / $countExamsWithMarks, 2) : 0 }} @if($highlight)*@endif</td>
                <td>{{ $countExamsWithMarks ? round($subjectTotalPoints / $countExamsWithMarks, 2) : 0 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="summary-box">
    <strong>Total Marks (Best 7)*:</strong> {{ $row['total_marks'] }} &nbsp; | &nbsp;
    <strong>Total Points*:</strong> {{ $row['total_points'] }} &nbsp; | &nbsp;
    <strong>GPA*:</strong> {{ $row['gpa'] }} &nbsp; | &nbsp;
    <strong>Division:</strong> {{ $row['division'] }} &nbsp; | &nbsp;
    <strong>Position:</strong> {{ $row['position'] ?? '-' }}
</div>

<div class="comments">
    <strong>Class Teacher Comments:</strong> ________________________________<br>
    <strong>Head of Department Comments:</strong> ___________________________<br>
    <strong>Principal Comments:</strong> _________________________________
</div>

<div class="signature-section">
    <div class="signature-box">Class Teacher</div>
    <div class="signature-box">Head of Department</div>
    <div class="signature-box">Principal</div>
</div>



<div class="footer">
    <div class="left">
        Generated by: MEMA ASEP System | {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}
    </div>
    
</div>



<div class="page-break"></div>
@endforeach


</body>
</html>
