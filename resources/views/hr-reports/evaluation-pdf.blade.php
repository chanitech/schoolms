<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>HR Evaluation Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background-color: #007bff; color: white; }
        h2 { text-align: center; color: #007bff; }
    </style>
</head>
<body>
    <h2>HR Evaluation Report</h2>

    <table>
        <thead>
            <tr>
                <th>Staff Name</th>
                <th>Department</th>
                <th>Attendance (%)</th>
                <th>Job Card (%)</th>
                <th>Leaves</th>
                <th>Events</th>
                <th>Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach($evaluations as $eval)
                <tr>
                    <td>{{ $eval['staff_name'] }}</td>
                    <td>{{ $eval['department'] }}</td>
                    <td>{{ $eval['attendance'] }}</td>
                    <td>{{ $eval['job_card_rate'] }}</td>
                    <td>{{ $eval['leaves_taken'] }}</td>
                    <td>{{ $eval['event_participation'] }}</td>
                    <td><strong>{{ $eval['score'] }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
