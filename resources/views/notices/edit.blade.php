@extends('adminlte::page')

@section('title', 'Edit Notice')

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-bullhorn mr-2"></i>Edit Notice</h1>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    @if($errors->any())
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>
    @endif

    <div class="card card-outline card-primary shadow-sm">
        <form method="POST" action="{{ route('notices.update', $notice) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required maxlength="200" value="{{ old('title', $notice->title) }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Audience <span class="text-danger">*</span></label>
                        <select name="audience" class="form-control" required>
                            <option value="all" @selected(old('audience', $notice->audience) == 'all')>Everyone</option>
                            <option value="staff" @selected(old('audience', $notice->audience) == 'staff')>Staff only</option>
                            <option value="guardians" @selected(old('audience', $notice->audience) == 'guardians')>Guardians only</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Notice <span class="text-danger">*</span></label>
                    <textarea name="body" rows="6" class="form-control" required maxlength="3000">{{ old('body', $notice->body) }}</textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Expires on <span class="text-muted">(optional)</span></label>
                        <input type="date" name="expires_at" class="form-control"
                               value="{{ old('expires_at', $notice->expires_at?->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group col-md-8 d-flex align-items-end">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="pinned" name="pinned" value="1"
                                   @checked(old('pinned', $notice->pinned))>
                            <label class="custom-control-label" for="pinned">Pin to top</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button>
                <a href="{{ route('notices.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@stop
