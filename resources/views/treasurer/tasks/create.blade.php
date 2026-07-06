@extends('adminlte::page')

@section('title', 'Assign Task')

@section('content_header')
    <h1 class="m-0 text-dark">Assign Task</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body">
            <form action="{{ route('treasurer.tasks.store') }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="user_id">Assign To <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                            <option value="">— Select —</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->getRoleNames()->implode(', ') }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="role">Role Context <span class="text-danger">*</span></label>
                        <input type="text" name="role" id="role" class="form-control @error('role') is-invalid @enderror" value="{{ old('role') }}" placeholder="e.g. storekeeper" required>
                        @error('role') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="task_description">Task Description <span class="text-danger">*</span></label>
                    <textarea name="task_description" id="task_description" rows="3" class="form-control @error('task_description') is-invalid @enderror" required>{{ old('task_description') }}</textarea>
                    @error('task_description') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="deadline">Deadline <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="deadline" id="deadline" class="form-control @error('deadline') is-invalid @enderror" value="{{ old('deadline') }}" required>
                    @error('deadline') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Assign Task</button>
                <a href="{{ route('treasurer.tasks.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@stop
