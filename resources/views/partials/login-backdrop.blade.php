{{-- Login backdrop: three photos fade slowly behind the form, under a navy tint.
     1 students with their teacher (Pexels, finix photographer)
     2 student receiving her certificate (Pexels, Bwalya Marcel Ngosa)
     3 parent celebrating a graduate (Unsplash, Imani Manyara) --}}
<style>
.login-backdrop { position: fixed; inset: 0; z-index: -1; overflow: hidden; background: #1b2956; }
.login-backdrop .slide {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
    opacity: 0;
    animation: loginSlide 21s infinite;
}
.login-backdrop .slide:nth-child(1) { background-image: url("{{ asset('images/brand/login-bg-1.jpg') }}"); animation-delay: 0s; }
.login-backdrop .slide:nth-child(2) { background-image: url("{{ asset('images/brand/login-bg-2.jpg') }}"); animation-delay: 7s; }
.login-backdrop .slide:nth-child(3) { background-image: url("{{ asset('images/brand/login-bg-3.jpg') }}"); animation-delay: 14s; }
.login-backdrop::after {
    content: ""; position: absolute; inset: 0;
    background: linear-gradient(rgba(19, 29, 61, .78), rgba(27, 41, 86, .62));
}
@keyframes loginSlide {
    0%   { opacity: 0; }
    6%   { opacity: 1; }
    33%  { opacity: 1; }
    40%  { opacity: 0; }
    100% { opacity: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .login-backdrop .slide { animation: none; }
    .login-backdrop .slide:nth-child(1) { opacity: 1; }
}
</style>
<div class="login-backdrop" aria-hidden="true">
    <div class="slide"></div>
    <div class="slide"></div>
    <div class="slide"></div>
</div>
