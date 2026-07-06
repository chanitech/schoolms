<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff &amp; Admin Login</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    min-height: 100vh;
    background: linear-gradient(145deg, #0f2942 0%, #16324f 55%, #1f5c3f 100%);
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
}

.card {
    background: #fff;
    border-radius: 20px;
    width: 100%; max-width: 400px;
    box-shadow: 0 20px 60px rgba(0,0,0,.35);
    overflow: hidden;
}

.card-top {
    background: linear-gradient(135deg, #0f2942, #16324f);
    padding: 2.2rem 2rem 1.8rem;
    text-align: center; color: #fff;
}
.school-logo {
    width: 72px; height: 72px; border-radius: 22px;
    margin: 0 auto .9rem;
    display: block;
}
.card-top h2 { font-size: 1.25rem; font-weight: 800; }
.card-top h2 .accent { color: #c9a24b; }
.card-top p  { font-size: .82rem; opacity: .8; margin-top: .3rem; }

.card-body { padding: 2rem; }

.form-group { margin-bottom: 1.2rem; }
label {
    display: block; font-size: .8rem; font-weight: 700;
    color: #334155; margin-bottom: .4rem; text-transform: uppercase; letter-spacing: .04em;
}

.input-wrap { position: relative; }
.input-wrap .icon {
    position: absolute; left: .9rem; top: 50%; transform: translateY(-50%);
    color: #94a3b8; font-size: 1rem; pointer-events: none;
}
input[type="text"], input[type="email"], input[type="password"] {
    width: 100%; padding: .75rem 1rem .75rem 2.6rem;
    border: 1.8px solid #e2e8f0; border-radius: 10px;
    font-size: .95rem; color: #1e293b;
    transition: border-color .2s, box-shadow .2s;
    outline: none;
}
input:focus {
    border-color: #c9a24b;
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
    border-radius: 8px; padding: .6rem .9rem;
    color: #dc2626; font-size: .8rem; margin-bottom: 1rem;
    display: flex; align-items: flex-start; gap: .5rem;
}

.remember-row {
    display: flex; align-items: center; gap: .5rem;
    margin-bottom: 1.4rem;
}
.remember-row input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; accent-color: #1f5c3f; }
.remember-row label { font-size: .82rem; color: #64748b; text-transform: none; letter-spacing: 0; font-weight: 500; margin: 0; cursor: pointer; }

.btn-login {
    width: 100%; padding: .85rem;
    background: linear-gradient(135deg, #0f2942, #1f5c3f);
    color: #fff; border: none; border-radius: 10px;
    font-size: 1rem; font-weight: 700; cursor: pointer;
    letter-spacing: .03em;
    transition: opacity .2s, transform .1s;
}
.btn-login:hover   { opacity: .9; }
.btn-login:active  { transform: scale(.98); }
.btn-login:disabled { opacity: .6; cursor: not-allowed; }

.divider { border: none; border-top: 1px solid #f1f5f9; margin: 1.3rem 0 1rem; }

.staff-link {
    text-align: center; font-size: .8rem; color: #94a3b8;
}
.staff-link a { color: #1f5c3f; font-weight: 600; text-decoration: none; }
.staff-link a:hover { text-decoration: underline; }

.forgot-row { text-align: right; margin-top: -.7rem; margin-bottom: 1.1rem; }
.forgot-row a { font-size: .78rem; color: #94a3b8; text-decoration: none; }
.forgot-row a:hover { color: #1f5c3f; text-decoration: underline; }
</style>
</head>
<body>

<div class="card">
    <div class="card-top">
        <img src="{{ asset('images/schoolms-icon.svg') }}" alt="SchoolMS" class="school-logo">
        <h2>School<span class="accent">MS</span></h2>
        <p>Staff &amp; Admin Portal</p>
    </div>

    <div class="card-body">

        @if($errors->any())
        <div class="error-msg">
            <span>&#9888;</span>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="error-msg" style="background:#fff8e1;border-color:#f6c23e;color:#856404">
            <span>&#8987;</span>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <form method="POST" action="{{ url('login') }}" id="loginForm">
            @csrf

            <div class="form-group">
                <label for="school_code">School Code</label>
                <div class="input-wrap">
                    <span class="icon">&#127979;</span>
                    <input
                        type="text"
                        id="school_code"
                        name="school_code"
                        value="{{ old('school_code') }}"
                        placeholder="e.g. kass"
                        autocomplete="off"
                        autofocus
                        class="{{ $errors->has('school_code') ? 'is-invalid' : '' }}"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email or Phone</label>
                <div class="input-wrap">
                    <span class="icon">&#128100;</span>
                    <input
                        type="text"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Email address or phone number"
                        autocomplete="username"
                        inputmode="email"
                        class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <span class="icon">&#128274;</span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                    >
                    <button type="button" class="toggle-pass" onclick="togglePass()" title="Show/hide password">
                        <span id="toggleIcon">&#128065;</span>
                    </button>
                </div>
            </div>

            <div class="forgot-row">
                <a href="{{ route('password.request') }}">Forgot password?</a>
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">Keep me logged in</label>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                Sign In
            </button>
        </form>

        <hr class="divider">
        <div class="staff-link">
            Parent / Guardian? <a href="{{ route('guardian.login') }}">Login here &rarr;</a>
        </div>
    </div>
</div>

<script>
function togglePass() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
}

document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('loginBtn');
    btn.disabled = true;
    btn.textContent = 'Signing in…';
});
</script>
</body>
</html>
