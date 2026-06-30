@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@endsection

@section('auth_header', 'Reset Your Password')

@section('auth_body')
<p class="text-muted text-center mb-3" style="font-size:.85rem">
    Enter your email address or phone number and we'll send a reset link to your email.
</p>

@if(session('status'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fas fa-check-circle mr-1"></i>{{ session('status') }}
</div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="input-group mb-3">
        <input
            type="text"
            name="login"
            class="form-control @error('login') is-invalid @enderror"
            value="{{ old('login') }}"
            placeholder="Email address or phone number"
            autocomplete="username"
            autofocus
        >
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-user"></span>
            </div>
        </div>
        @error('login')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="row">
        <div class="col-12">
            <button type="submit" class="btn btn-block btn-flat btn-primary">
                <i class="fas fa-paper-plane mr-1"></i> Send Reset Link
            </button>
        </div>
    </div>
</form>
@endsection

@section('auth_footer')
<p class="my-0">
    <a href="{{ route('login') }}"><i class="fas fa-arrow-left mr-1"></i> Back to login</a>
</p>
@endsection
