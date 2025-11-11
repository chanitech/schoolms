<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f9f9f9; }
        h3 { margin-bottom: 5px; }
    </style>
</head>
<body>
    <h3>Attendance Report</h3>
    <table>
        <thead>
            <tr>
                <th>Staff</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $a)
                <tr>
                    <td>{{ $a->staff->name }}</td>
                    <td>{{ $a->date->format('Y-m-d') }}</td>
                    <td>{{ ucfirst($a->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
