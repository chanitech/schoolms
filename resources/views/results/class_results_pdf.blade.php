<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Results</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        header img {
            max-height: 80px;
            margin-bottom: 10px;
        }

        h2, h3, h4 {
            margin: 5px 0;
        }

        .summary, .division-summary {
            display: flex;
            justify-content: space-around;
            margin: 15px 0;
        }

        .summary-box, .division-box {
            background-color: #f7f7f7;
            border: 1px solid #ddd;
            padding: 10px 15px;
            text-align: center;
            border-radius: 5px;
            width: 25%;
        }

        .summary-box h3, .division-box h3 {
            margin: 0;
            color: #28a745;
        }

        .summary-box p, .division-box p {
            margin: 3px 0 0;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 5px 8px;
            text-align: center;
            font-size: 12px;
        }

        th {
            background-color: #4CAF50;
            color: #fff;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tbody tr:hover {
            background-color: #d1ecf1;
        }

        footer {
            margin-top: 20px;
            text-align: center;
            font-size: 11px;
            color: #555;
        }

        .chart-container {
            width: 50%;
            margin: 20px auto;
        }
    </style>
</head>
<body>

<header>
    <img src="{{ public_path('vendor/adminlte/dist/img/MEMA.webp') }}" alt="School Logo">
    <h2>School Name</h2>
    <h3>Class Results</h3>
    <h4>
        @if(isset($classes))
            {{ $classes->firstWhere('id', $selectedClassId)->name ?? '' }}
        @endif
        @if(isset($exams))
            - {{ $exams->firstWhere('id', $selectedExamId)->name ?? '' }}
        @endif
    </h4>
</header>

{{-- Summary Boxes --}}
<div class="summary">
    <div class="summary-box">
        <h3>{{ count($studentsData) }}</h3>
        <p>Total Students</p>
    </div>
    <div class="summary-box">
        <h3>{{ !empty($studentsData) ? number_format(collect($studentsData)->avg('gpa'), 2) : 'N/A' }}</h3>
        <p>Average GPA</p>
    </div>
    <div class="summary-box">
        <h3>{{ !empty($studentsData) ? max(collect($studentsData)->pluck('total_points')->toArray()) : 0 }}</h3>
        <p>Highest Total Points</p>
    </div>
</div>

{{-- Division Summary & Chart --}}
@php
    $divisionCounts = [];
    foreach($studentsData as $data) {
        $div = $data['division'] ?? 'N/A';
        if(!isset($divisionCounts[$div])) $divisionCounts[$div] = 0;
        $divisionCounts[$div]++;
    }
    $divisionLabels = array_keys($divisionCounts);
    $divisionValues = array_values($divisionCounts);
@endphp

<div class="division-summary">
    @foreach($divisionCounts as $div => $count)
        <div class="division-box">
            <h3>{{ $count }}</h3>
            <p>{{ $div }}</p>
        </div>
    @endforeach
</div>

<div class="chart-container">
    <canvas id="divisionChart"></canvas>
</div>

{{-- Results Table --}}
<table>
    <thead>
        <tr>
            <th>Pos</th>
            <th>Student</th>
            @foreach($subjects as $subject)
                <th>{{ $subject->name }} Mark</th>
                <th>{{ $subject->name }} Grade</th>
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
                <td>{{ $data['student']->first_name }} {{ $data['student']->last_name }}</td>
                @foreach($subjects as $subject)
                    @php
                        $sub = $data['subjectsData'][$subject->id] ?? ['mark'=>0, 'grade'=>'-'];
                    @endphp
                    <td>{{ $sub['mark'] }}</td>
                    <td>{{ $sub['grade'] }}</td>
                @endforeach
                <td>{{ $data['total_points'] }}</td>
                <td>{{ number_format($data['gpa'], 2) }}</td>
                <td>{{ $data['division'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<footer>
    <p>Generated on {{ date('d-m-Y H:i') }}</p>
</footer>

<script>
    const ctx = document.getElementById('divisionChart').getContext('2d');
    const divisionChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: @json($divisionLabels),
            datasets: [{
                label: 'Division Count',
                data: @json($divisionValues),
                backgroundColor: [
                    '#4CAF50', '#2196F3', '#FFC107', '#FF5722', '#9C27B0'
                ],
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Division Distribution'
                }
            }
        }
    });
</script>

</body>
</html>
