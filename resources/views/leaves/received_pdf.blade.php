<!DOCTYPE html>
<html>
<head>
    <title>Received Leave Requests</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 5px; text-align: left; }
        th { background: #f0f0f0; }
        .badge { font-weight: bold; text-transform: capitalize; }
    </style>
</head>
<body>
    @include('partials.pdf-letterhead', ['documentTitle' => 'RECEIVED LEAVE REQUESTS'])

    <div style="font-size:9px; color:#555;">Generated {{ now()->format('d M Y H:i') }} — {{ $leaves->count() }} request(s)</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Staff</th>
                <th>Department</th>
                <th>Type</th>
                <th>Start</th>
                <th>End</th>
                <th>Days</th>
                <th>Status</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaves as $leave)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ trim(($leave->requester->first_name ?? '') . ' ' . ($leave->requester->last_name ?? '')) ?: '—' }}</td>
                <td>{{ $leave->requester->department->name ?? '—' }}</td>
                <td>{{ ucfirst($leave->type ?? '—') }}</td>
                <td>{{ $leave->start_date?->format('d M Y') }}</td>
                <td>{{ $leave->end_date?->format('d M Y') }}</td>
                <td>{{ $leave->start_date && $leave->end_date ? $leave->start_date->diffInDays($leave->end_date) + 1 : '—' }}</td>
                <td class="badge">{{ $leave->status }}</td>
                <td>{{ \Illuminate\Support\Str::limit($leave->reason, 60) }}</td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align:center;">No leave requests match the filters.</td></tr>
            @endforelse
        </tbody>
    </table>
@include('partials.pdf-powered-by')
</body>
</html>
