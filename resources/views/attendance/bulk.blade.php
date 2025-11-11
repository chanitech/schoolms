@extends('adminlte::page')

@section('title', 'Bulk Attendance')

@section('content_header')
    <h1>Bulk Attendance</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        {{-- Display validation errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('attendance.bulk.store') }}" method="POST">
            @csrf

            {{-- Date --}}
            <div class="form-group mb-3">
                <label>Date</label>
                <input 
                    type="date" 
                    name="date" 
                    class="form-control" 
                    value="{{ old('date', now()->toDateString()) }}" 
                    required>
            </div>

            {{-- Staff Attendance Table --}}
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $s)
                        <tr>
                            <td>{{ $s->name }}</td>
                            <td>
                                <select name="attendance[{{ $s->id }}]" class="form-control">
                                    <option value="present" {{ old('attendance.'.$s->id) == 'present' ? 'selected' : '' }}>Present</option>
                                    <option value="absent" {{ old('attendance.'.$s->id) == 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="leave" {{ old('attendance.'.$s->id) == 'leave' ? 'selected' : '' }}>Leave</option>
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">No staff available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                <button class="btn btn-success">Save Attendance</button>
                <a href="{{ route('attendance.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@stop
