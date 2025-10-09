@extends('adminlte::page')

@section('title', 'Edit Leave Request')

@section('content_header')
    <h1>Edit Leave Request</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('leaves.update', $leave) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="type" class="form-label">Leave Type</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="sick" {{ $leave->type=='sick'?'selected':'' }}>Sick</option>
                    <option value="casual" {{ $leave->type=='casual'?'selected':'' }}>Casual</option>
                    <option value="annual" {{ $leave->type=='annual'?'selected':'' }}>Annual</option>
                    <option value="other" {{ $leave->type=='other'?'selected':'' }}>Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $leave->start_date->format('Y-m-d') }}" required>
            </div>

            <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $leave->end_date->format('Y-m-d') }}" required>
            </div>

            <div class="mb-3">
                <label for="requested_to" class="form-label">Send To</label>
                <select name="requested_to" id="requested_to" class="form-control" required>
                    @foreach($recipients as $recipient)
                        <option value="{{ $recipient->id }}" {{ $leave->requested_to==$recipient->id?'selected':'' }}>
                            {{ $recipient->first_name }} {{ $recipient->last_name }} ({{ ucfirst($recipient->role) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="reason" class="form-label">Reason (Optional)</label>
                <textarea name="reason" id="reason" class="form-control" rows="3">{{ $leave->reason }}</textarea>
            </div>

            <button type="submit" class="btn btn-success">Update Leave</button>
            <a href="{{ route('leaves.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop
