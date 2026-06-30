@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('auth_header', 'Session Expired')

@section('auth_body')
<div class="text-center mb-3">
    <i class="fas fa-clock fa-3x text-warning mb-3"></i>
    <p class="text-muted">Your session has expired or the page was open too long. Please sign in again.</p>
</div>
<a href="{{ route('login') }}" class="btn btn-block btn-flat btn-primary">
    <i class="fas fa-sign-in-alt mr-1"></i> Back to Login
</a>
@endsection
