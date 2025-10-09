@extends('adminlte::page')

@section('title', 'Request Leave')

@section('content_header')
    <h1>Request Leave</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('leaves.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="type" class="form-label">Leave Type</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="">-- Select Type --</option>
                    <option value="sick" {{ old('type')=='sick'?'selected':'' }}>Sick</option>
                    <option value="casual" {{ old('type')=='casual'?'selected':'' }}>Casual</option>
                    <option value="annual" {{ old('type')=='annual'?'selected':'' }}>Annual</option>
                    <option value="other" {{ old('type')=='other'?'selected':'' }}>Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date') }}" required>
            </div>

            <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}" required>
            </div>

            <div class="mb-3">
                <label for="requested_to" class="form-label">Send To</label>
                <select name="requested_to" id="requested_to" class="form-control" required>
                    <option value="">-- Select Recipient --</option>
                    @foreach($recipients as $recipient)
                        <option value="{{ $recipient->id }}" {{ old('requested_to')==$recipient->id?'selected':'' }}>
                            {{ $recipient->first_name }} {{ $recipient->last_name }} ({{ ucfirst($recipient->role) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="reason" class="form-label">Reason (Optional)</label>
                <textarea name="reason" id="reason" class="form-control" rows="3">{{ old('reason') }}</textarea>
            </div>

            <button type="submit" class="btn btn-success">Submit Request</button>
            <a href="{{ route('leaves.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop
