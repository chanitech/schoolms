<!DOCTYPE html>
<html>
<head>
    <title>Leave Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Leave Report</h2>
    <table>
        <thead>
            <tr>
                <th>Staff Name</th>
                <th>Department</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Total Days</th>
                <th>Status</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            @foreach($leaves as $leave)
            <tr>
                <td>{{ $leave->requester->name }}</td>
                <td>{{ $leave->requester->department->name ?? 'N/A' }}</td>
                <td>{{ $leave->type }}</td>
                <td>{{ $leave->start_date }}</td>
                <td>{{ $leave->end_date }}</td>
                <td>{{ $leave->total_days }}</td>
                <td>{{ ucfirst($leave->status) }}</td>
                <td>{{ $leave->reason }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
