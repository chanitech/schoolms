@extends('adminlte::page')

@section('title', 'Add Timetable Period')

@section('content_header')
    <h1>Add Timetable Period</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('timetable-periods.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="e.g., Period 1, Break, Lunch" required>
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="start_time">Start Time</label>
                        <input type="time" name="start_time" id="start_time"
                               class="form-control @error('start_time') is-invalid @enderror"
                               value="{{ old('start_time') }}" required>
                        @error('start_time') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="end_time">End Time</label>
                        <input type="time" name="end_time" id="end_time"
                               class="form-control @error('end_time') is-invalid @enderror"
                               value="{{ old('end_time') }}" required>
                        @error('end_time') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="order_no">Order</label>
                    <input type="number" name="order_no" id="order_no" min="0" max="255"
                           class="form-control @error('order_no') is-invalid @enderror"
                           value="{{ old('order_no', $nextOrder) }}" required>
                    <small class="form-text text-muted">Controls the row order on the daily schedule — lower numbers appear first.</small>
                    @error('order_no') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" name="is_break" id="is_break" class="form-check-input" value="1" {{ old('is_break') ? 'checked' : '' }}>
                    <label for="is_break" class="form-check-label">This is a break (Assembly, Short Break, Lunch, etc.) — not a teaching period</label>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label for="is_active" class="form-check-label">Active (counts toward timetable generation and capacity)</label>
                </div>

                <button type="submit" class="btn btn-success mt-2">Save Period</button>
                <a href="{{ route('timetable-periods.index') }}" class="btn btn-secondary mt-2">Cancel</a>
            </form>
        </div>
    </div>
@stop
