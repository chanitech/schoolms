<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Access Required</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="card card-outline card-danger">
        <div class="card-header text-center">
            <i class="fas fa-crown text-warning" style="font-size:2rem;"></i>
            <h4 class="mt-2 mb-0">Super Admin Only</h4>
        </div>
        <div class="card-body text-center">
            <p class="text-muted mb-1">You are logged in as:</p>
            <p class="font-weight-bold">{{ $user->name }}</p>
            <p class="text-muted small mb-3">{{ $user->email }}</p>
            <p class="text-danger mb-4">This account does not have super admin access.</p>

            <form method="POST" action="{{ route('logout') }}" class="mb-3">
                @csrf
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-out-alt mr-1"></i> Log out &amp; switch account
                </button>
            </form>

            <a href="{{ url()->previous() }}" class="btn btn-secondary btn-block">
                <i class="fas fa-arrow-left mr-1"></i> Go back
            </a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/plugins/jquery/jquery.min.js"></script>
</body>
</html>
