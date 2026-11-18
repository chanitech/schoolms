{{-- Login backdrop: Zanzibar schoolgirls in Stone Town (Pexels, free licence) under a navy tint --}}
<style>
.login-backdrop {
    position: fixed; inset: 0; z-index: -1;
    background: #1b2956 url("{{ asset('images/brand/login-bg-4.jpg') }}") center 55% / cover no-repeat;
}
.login-backdrop::after {
    content: ""; position: absolute; inset: 0;
    background: linear-gradient(rgba(19, 29, 61, .78), rgba(27, 41, 86, .62));
}
</style>
<div class="login-backdrop" aria-hidden="true"></div>
