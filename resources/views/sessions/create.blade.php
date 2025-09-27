@extends('adminlte::page')

@section('title', 'Add Academic Session')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-calendar-alt"></i> Add Academic Session</h1>
@stop

@section('content')
    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('sessions.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Session Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g., 2025/2026">
                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                        @error('start_date') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                        @error('end_date') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="is_current" class="form-check-input" id="is_current" {{ old('is_current') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_current">Set as Current Session</label>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Session</button>
            </form>
        </div>
    </div>
@stop
