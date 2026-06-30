@extends('adminlte::auth.login')

@section('auth_body')
@if(session('error'))
<div class="alert alert-warning alert-dismissible mb-3 py-2">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fas fa-clock mr-1"></i>{{ session('error') }}
</div>
@endif
<form action="{{ url('login') }}" method="post">
    @csrf

    {{-- Email or Phone field --}}
    <div class="input-group mb-3">
        <input
            type="text"
            name="email"
            class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email') }}"
            placeholder="Email address or phone number"
            autocomplete="username"
            inputmode="email"
            autofocus
        >
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-user"></span>
            </div>
        </div>
        @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    {{-- Password field --}}
    <div class="input-group mb-3">
        <input
            type="password"
            name="password"
            class="form-control @error('password') is-invalid @enderror"
            placeholder="Password"
            autocomplete="current-password"
        >
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-lock"></span>
            </div>
        </div>
        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    {{-- Remember me + Submit --}}
    <div class="row">
        <div class="col-7">
            <div class="icheck-primary">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">Remember me</label>
            </div>
        </div>
        <div class="col-5">
            <button type="submit" class="btn btn-block btn-flat btn-primary">
                <span class="fas fa-sign-in-alt"></span> Sign In
            </button>
        </div>
    </div>
</form>
@endsection

@section('auth_footer')
<p class="my-0">
    <a href="{{ route('password.request') }}">
        <i class="fas fa-key mr-1"></i>Forgot my password
    </a>
</p>
@endsection

