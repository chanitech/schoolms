@extends('adminlte::page')

@section('title', 'Bulk Attendance')

@section('content_header')
    <h1>Bulk Attendance</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('attendance.bulk.store') }}" method="POST">
            @csrf

            <div class="form-group mb-3">
                <label>Date</label>
                <input type="date" name="date" class="form-control" required>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staff as $s)
                        <tr>
                            <td>{{ $s->name }}</td>
                            <td>
                                <select name="attendance[{{ $s->id }}]" class="form-control">
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="leave">Leave</option>
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <button class="btn btn-success">Save Attendance</button>
            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop
