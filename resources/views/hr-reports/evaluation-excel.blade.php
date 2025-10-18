<table>
    <thead>
        <tr style="background-color:#007bff; color:white;">
            <th>Staff Name</th>
            <th>Department</th>
            <th>Attendance (%)</th>
            <th>Job Card Completion (%)</th>
            <th>Leaves Taken</th>
            <th>Event Participation</th>
            <th>Overall Score</th>
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
                <td>{{ $eval['score'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
