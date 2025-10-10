<!DOCTYPE html>
<html>
<head>
    <title>School Events</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h3>School Events</h3>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Department</th>
                <th>Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $event)
            <tr>
                <td>{{ $event->title }}</td>
                <td>{{ $event->department?->name ?? 'All' }}</td>
                <td>{{ ucfirst($event->type) }}</td>
                <td>{{ $event->start_date->format('Y-m-d') }}</td>
                <td>{{ $event->end_date->format('Y-m-d') }}</td>
                <td>{{ $event->description }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
