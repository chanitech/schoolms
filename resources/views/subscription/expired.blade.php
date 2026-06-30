<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { background: #fff; border-radius: 12px; padding: 3rem; max-width: 480px; text-align: center; box-shadow: 0 4px 24px rgba(0,0,0,.1); }
        .icon { font-size: 4rem; color: #ef4444; margin-bottom: 1rem; }
        h1 { font-size: 1.6rem; color: #111827; margin-bottom: .5rem; }
        p  { color: #6b7280; line-height: 1.6; margin-bottom: 1.5rem; }
        .btn { display: inline-block; padding: .7rem 1.8rem; background: #0d5b47; color: #fff; border-radius: 6px; text-decoration: none; font-weight: 700; }
        .logout { display: block; margin-top: 1rem; color: #9ca3af; font-size: .85rem; text-decoration: none; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon"><i class="fas fa-lock"></i></div>
        <h1>Subscription Expired</h1>
        <p>Your school's subscription has expired or been cancelled. Please contact your system administrator or the MEMANexus support team to renew access.</p>
        <a href="mailto:support@memanexus.com" class="btn">
            <i class="fas fa-envelope mr-1"></i> Contact Support
        </a>
        <a href="{{ route('logout') }}" class="logout"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit()">
            Sign out
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">
            @csrf
        </form>
    </div>
</body>
</html>
