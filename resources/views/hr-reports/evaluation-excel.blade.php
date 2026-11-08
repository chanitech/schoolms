<table>
    <thead>
            <tr>
                <th>Staff Name</th>
                <th>Department</th>
                <th>Attendance</th>
                <th>Job Cards</th>
                <th>Topic Coverage</th>
                <th>Session Attendance</th>
                <th>Overall Score</th>
            </tr>
        </thead>
    <tbody>
        @foreach($evaluations as $eval)
            <tr>
                    <td>{{ $eval->staff_name }}</td>
                    <td>{{ $eval->department }}</td>
                    <td>{{ $eval->attendance }}%</td>
                    <td>{{ $eval->job_card_rate }}%</td>
                    <td>{{ $eval->lesson_rate !== null ? $eval->lesson_rate . '%' : 'N/A' }}</td>
                    <td>{{ $eval->session_rate !== null ? $eval->session_rate . '%' : 'N/A' }}</td>
                    <td>{{ $eval->score }}</td>
                </tr>
        @endforeach
    </tbody>
</table>
