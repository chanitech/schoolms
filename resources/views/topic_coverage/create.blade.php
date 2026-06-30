@extends('adminlte::page')

@section('title', 'New Topic Coverage')

@section('content_header')
<div class="d-flex align-items-center">
    <a href="{{ route('topic-coverage.index') }}" class="btn btn-secondary btn-sm mr-3">
        <i class="fas fa-arrow-left mr-1"></i>Back
    </a>
    <h1 class="mb-0"><i class="fas fa-tasks text-primary mr-2"></i>New Topic Coverage Record</h1>
</div>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('topic-coverage.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-bold">Academic Year <span class="text-danger">*</span></label>
                        <select name="academic_session_id" class="form-control select2" required>
                            <option value="">— Select Year —</option>
                            @foreach($sessions as $s)
                            <option value="{{ $s->id }}" {{ old('academic_session_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('academic_session_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Class <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-control select2" required>
                            <option value="">— Select Class —</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-control select2" required>
                            <option value="">— Select Subject —</option>
                            @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ old('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Title <small class="text-muted">(optional)</small></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}"
                               placeholder="e.g. Form 4 Biology Term 1">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Description <small class="text-muted">(optional)</small></label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Any notes about this coverage record…">{{ old('description') }}</textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('topic-coverage.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-arrow-right mr-1"></i>Create &amp; Add Topics
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet"/>
@endpush
@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>$(function(){ $('.select2').select2({ theme:'bootstrap4', width:'100%' }); });</script>
@endpush
