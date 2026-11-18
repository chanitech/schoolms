{{-- Login backdrop: Tanzania flag drawn in CSS (no image to download), softened so the form stays the focus.
     "to bottom right" puts the 50% line on the bottom-left → top-right diagonal, as on the flag. --}}
<style>
.login-backdrop {
    position: fixed; inset: 0; z-index: -1;
    background: linear-gradient(to bottom right,
        #1eb53a 0 37%,
        #fcd116 37% 40.5%,
        #000 40.5% 59.5%,
        #fcd116 59.5% 63%,
        #00a3dd 63% 100%);
}
.login-backdrop::after {
    content: ""; position: absolute; inset: 0;
    background: rgba(13, 20, 43, .35);
}
</style>
<div class="login-backdrop" aria-hidden="true"></div>
