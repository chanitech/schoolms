<footer class="main-footer d-flex flex-wrap justify-content-between align-items-center small text-muted">
    <div>
        &copy; {{ date('Y') }} <strong>{{ config('app.name', 'ShulePRO') }}</strong>. All rights reserved.
    </div>
    <div>
        @yield('footer')
        Powered by <a href="https://chanitech.co.tz" target="_blank" rel="noopener">CHANI Technologies</a>
    </div>
</footer>
