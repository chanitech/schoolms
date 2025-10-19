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
                    <input type="text" name="name" value="{{ $department->name }}" class="form-control" required>
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
                    <textarea name="description" class="form-control">{{ $department->description }}</textarea>
                </div>

                <button type="submit" class="btn btn-success">Update</button>
                <a href="{{ route('departments.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@stop
