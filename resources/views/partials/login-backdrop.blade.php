{{-- Login backdrop: dhow on the turquoise water of Zanzibar (Pexels, Taryn Elliott, free licence).
     1280px / ~124 KB; the sea-blue fallback shows instantly while it loads. --}}
<style>
.login-backdrop {
    position: fixed; inset: 0; z-index: -1;
    background: #1d6f86 url("{{ asset('images/brand/login-znz.jpg') }}") center / cover no-repeat;
}
.login-backdrop::after {
    content: ""; position: absolute; inset: 0;
    background: linear-gradient(rgba(19, 29, 61, .45), rgba(19, 29, 61, .30));
}
</style>
<div class="login-backdrop" aria-hidden="true"></div>
