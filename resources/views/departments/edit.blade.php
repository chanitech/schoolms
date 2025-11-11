@extends('adminlte::page')

@section('title','Edit Department')

@section('content_header')
<h1 class="text-center text-success">Edit Department</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('departments.update', $department->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name">Department Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $department->name) }}" required>
                </div>

                <div class="mb-3">
                    <label for="head_id">Department Head</label>
                    <select name="head_id" class="form-control">
                        <option value="">-- Select HOD --</option>
                        @foreach($hods as $hod)
                            <option value="{{ $hod->id }}" {{ $department->head_id == $hod->id ? 'selected' : '' }}>
                                {{ $hod->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="description">Description</label>
                    <textarea name="description" class="form-control">{{ old('description', $department->description) }}</textarea>
                </div>

                <!-- Rank requires 7 subjects checkbox -->
                <div class="mb-3 form-check">
                    <input type="checkbox" name="rank_requires_7_subjects" class="form-check-input" id="rank_requires_7_subjects" value="1"
                        {{ $department->rank_requires_7_subjects ? 'checked' : '' }}>
                    <label class="form-check-label" for="rank_requires_7_subjects">
                        Require 7 subjects for ranking
                    </label>
                </div>

                <button type="submit" class="btn btn-success">Update</button>
                <a href="{{ route('departments.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@stop
