@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@endsection

@section('auth_header', 'Set New Password')

@section('auth_body')
<form method="POST" action="{{ route('password.store') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    {{-- Email (pre-filled from reset link, hidden from user) --}}
    <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

    @error('email')
    <div class="alert alert-danger py-2 mb-3" style="font-size:.82rem">
        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
    </div>
    @enderror

    <div class="input-group mb-3">
        <input
            type="password"
            name="password"
            class="form-control @error('password') is-invalid @enderror"
            placeholder="New password"
            autocomplete="new-password"
            autofocus
        >
        <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-lock"></span></div>
        </div>
        @error('password')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="input-group mb-3">
        <input
            type="password"
            name="password_confirmation"
            class="form-control @error('password_confirmation') is-invalid @enderror"
            placeholder="Confirm new password"
            autocomplete="new-password"
        >
        <div class="input-group-append">
            <div class="input-group-text"><span class="fas fa-lock"></span></div>
        </div>
        @error('password_confirmation')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="row">
        <div class="col-12">
            <button type="submit" class="btn btn-block btn-flat btn-primary">
                <i class="fas fa-key mr-1"></i> Reset Password
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
