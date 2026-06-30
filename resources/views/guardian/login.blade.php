<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Parent / Guardian Login</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    min-height: 100vh;
    background: linear-gradient(145deg, #0f2044 0%, #1a3c6e 50%, #1565c0 100%);
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
    background: linear-gradient(135deg, #1a3c6e, #2e74c0);
    padding: 2.2rem 2rem 1.8rem;
    text-align: center; color: #fff;
}
.school-logo {
    width: 64px; height: 64px; border-radius: 50%;
    background: rgba(255,255,255,.15);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 2rem; margin-bottom: .8rem;
    border: 3px solid rgba(255,255,255,.3);
}
.card-top h2 { font-size: 1.25rem; font-weight: 800; }
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
input[type="text"], input[type="tel"], input[type="password"] {
    width: 100%; padding: .75rem 1rem .75rem 2.6rem;
    border: 1.8px solid #e2e8f0; border-radius: 10px;
    font-size: .95rem; color: #1e293b;
    transition: border-color .2s, box-shadow .2s;
    outline: none;
}
input:focus {
    border-color: #2e74c0;
    box-shadow: 0 0 0 3px rgba(46,116,192,.15);
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
.remember-row input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; accent-color: #2e74c0; }
.remember-row label { font-size: .82rem; color: #64748b; text-transform: none; letter-spacing: 0; font-weight: 500; margin: 0; cursor: pointer; }

.btn-login {
    width: 100%; padding: .85rem;
    background: linear-gradient(135deg, #1a3c6e, #2e74c0);
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
.staff-link a { color: #2e74c0; font-weight: 600; text-decoration: none; }
.staff-link a:hover { text-decoration: underline; }

.hint {
    font-size: .73rem; color: #94a3b8; margin-top: .3rem;
}
</style>
</head>
<body>

<div class="card">
    <div class="card-top">
        <div class="school-logo">&#127979;</div>
        @php $school = \App\Models\SchoolInfo::first(); @endphp
        <h2>{{ $school->name ?? 'School Management System' }}</h2>
        <p>Parent &amp; Guardian Portal</p>
    </div>

    <div class="card-body">

        @if($errors->any())
        <div class="error-msg">
            <span>&#9888;</span>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

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

        <form method="POST" action="{{ route('guardian.login.post') }}" id="loginForm">
            @csrf

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <div class="input-wrap">
                    <span class="icon">&#128222;</span>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="{{ old('phone') }}"
                        placeholder="e.g. 0712 345 678"
                        autocomplete="tel"
                        autofocus
                        class="{{ $errors->has('phone') ? 'is-invalid' : '' }}"
                        inputmode="tel"
                    >
                </div>
                <p class="hint">Enter the phone number registered with the school.</p>
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
            Staff / Admin? <a href="{{ route('login') }}">Login here &rarr;</a>
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
