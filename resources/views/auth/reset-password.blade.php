<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Set New Password</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family:-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    min-height: 100vh;
    background:#f4f6f9;
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
}

.card {
    background: #fff;
    border-radius: .5rem;
    width: 100%; max-width: 400px;
    box-shadow:0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    overflow: hidden;
}

.card-top {
    background:#1b2956;
    padding: 2.2rem 2rem 1.8rem;
    text-align: center; color: #fff;
}
.school-logo {
    height: 64px; width: auto;
    margin: 0 auto .9rem;
    display: block;
}
.card-top h2 { font-size: 1.25rem; font-weight: 800; }
.card-top h2 .accent { color: #FBB516; }
.card-top p  { font-size: .82rem; opacity: .8; margin-top: .3rem; }

.card-body { padding: 2rem; }

.form-group { margin-bottom: 1.2rem; }
label {
    display: block; font-size: .8rem; font-weight: 700;
    color: #334155; margin-bottom: .4rem; text-transform: uppercase;
}

.input-wrap { position: relative; }
.input-wrap .icon {
    position: absolute; left: .9rem; top: 50%; transform: translateY(-50%);
    color: #94a3b8; font-size: 1rem; pointer-events: none;
}
input[type="password"] {
    width: 100%; padding: .75rem 1rem .75rem 2.6rem;
    border: 1.8px solid #e2e8f0; border-radius:.25rem;
    font-size: .95rem; color: #1e293b;
    outline: none;
}
input:focus {
    border-color: #fbb516;
    box-shadow: 0 0 0 3px rgba(201,162,75,.18);
}
input.is-invalid { border-color: #ef4444; }

.toggle-pass {
    position: absolute; right: .9rem; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; color: #94a3b8;
    font-size: .9rem; padding: 0;
}

.error-msg {
    background: #fef2f2; border: 1px solid #fecaca;
    border-radius:.25rem; padding: .6rem .9rem;
    color: #dc2626; font-size: .8rem; margin-bottom: 1rem;
    display: flex; align-items: flex-start; gap: .5rem;
}

.btn-login {
    width: 100%; padding: .85rem;
    background:#1b2956;
    color: #fff; border: none; border-radius:.25rem;
    font-size: 1rem; font-weight: 700; cursor: pointer;
}
.btn-login:hover   { opacity: .9; }
.btn-login:active  { transform: scale(.98); }
.btn-login:disabled { opacity: .6; cursor: not-allowed; }

.divider { border: none; border-top: 1px solid #f1f5f9; margin: 1.3rem 0 1rem; }

.staff-link { text-align: center; font-size: .8rem; color: #94a3b8; }
.staff-link a { color: #1b2956; font-weight: 600; text-decoration: none; }
.staff-link a:hover { text-decoration: underline; }
</style>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
</head>
<body>

<div class="card">
    <div class="card-top">
        <img src="{{ asset('images/brand/shulepro-icon-white.png') }}" alt="ShulePRO" class="school-logo">
        <h2>Shule<span class="accent">PRO</span></h2>
        <p>Set New Password</p>
    </div>

    <div class="card-body">

        @if($errors->any())
        <div class="error-msg">
            <span><i class="fas fa-exclamation-triangle"></i></span>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}" id="resetForm">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

            <div class="form-group">
                <label for="password">New Password</label>
                <div class="input-wrap">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter new password"
                        autocomplete="new-password"
                        autofocus
                        class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                    >
                    <button type="button" class="toggle-pass" onclick="togglePass('password','toggleIcon1')" title="Show/hide password">
                        <span id="toggleIcon1"><i class="fas fa-eye"></i></span>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm New Password</label>
                <div class="input-wrap">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Re-enter new password"
                        autocomplete="new-password"
                        class="{{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                    >
                    <button type="button" class="toggle-pass" onclick="togglePass('password_confirmation','toggleIcon2')" title="Show/hide password">
                        <span id="toggleIcon2"><i class="fas fa-eye"></i></span>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login" id="resetBtn">
                Reset Password
            </button>
        </form>

        <hr class="divider">
        <div class="staff-link">
            <a href="{{ route('login') }}">&larr; Back to login</a>
        </div>
    </div>
</div>

<script>
function togglePass(inputId, iconId) {
    const input = document.getElementById(inputId);
    input.type = input.type === 'password' ? 'text' : 'password';
}

document.getElementById('resetForm').addEventListener('submit', function() {
    const btn = document.getElementById('resetBtn');
    btn.disabled = true;
    btn.textContent = 'Resetting…';
});
</script>
</body>
</html>
