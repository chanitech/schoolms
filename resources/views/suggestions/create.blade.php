@extends('adminlte::page')

@section('title', 'New Suggestion')

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-comment-dots mr-2"></i>Send to Administration</h1>
@stop

@section('content')
<div class="container-fluid">
    @include('partials.flash')
    @if($errors->any())
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}</div>
    @endif

    <div class="card card-outline card-primary shadow-sm">
        <form method="POST" action="{{ route($baseRoute . '.store') }}">
            @csrf
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Type</label>
                        <select name="category" class="form-control" required>
                            <option value="suggestion">Suggestion</option>
                            <option value="opinion">Opinion</option>
                            <option value="complaint">Complaint</option>
                            <option value="compliment">Compliment</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group col-md-8">
                        <label>Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" required maxlength="200"
                               placeholder="A short summary" value="{{ old('subject') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>Your message <span class="text-danger">*</span></label>
                    <textarea name="message" rows="6" class="form-control" required maxlength="3000"
                              placeholder="Write as much detail as you'd like...">{{ old('message') }}</textarea>
                </div>

                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="anon" name="is_anonymous" value="1">
                    <label class="custom-control-label" for="anon">Submit anonymously</label>
                </div>
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    If checked, we do not record who sent this — not even the administration can see your name.
                    The tradeoff: you won't be able to see the administration's response afterwards, since there's
                    no way to link a reply back to an anonymous submission. Leave this unchecked if you'd like a reply.
                </small>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i> Send</button>
                <a href="{{ route($baseRoute . '.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@stop
