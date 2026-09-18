<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- IFrame Preloader Removal Workaround --}}
    <!-- IFrame Preloader Removal Workaround -->
    <style type="text/css">
        body.iframe-mode .preloader {
            display: none !important;
        }
    </style>

    {{-- Dark-mode readability fixes: many pages use Bootstrap's light-background
         utility classes (table-light, thead-light, bg-light, bg-white) for
         headers/cards. AdminLTE's dark-mode only recolors its own components,
         so these stayed white and their text (recolored white by dark-mode)
         became invisible. Also darken the top navbar, which is hardcoded to
         navbar-white/navbar-light in config and doesn't switch on its own. --}}
    <style type="text/css">
        body.dark-mode .table-light,
        body.dark-mode .table-light > th,
        body.dark-mode .table-light > td,
        body.dark-mode thead.thead-light th,
        body.dark-mode .thead-light th {
            background-color: #3a3f44 !important;
            color: #e9ecef !important;
            border-color: #4b5157 !important;
        }
        body.dark-mode .bg-light {
            background-color: #3a3f44 !important;
            color: #e9ecef !important;
        }
        body.dark-mode .bg-white {
            background-color: #2c3237 !important;
            color: #e9ecef !important;
        }
        body.dark-mode .main-header.navbar {
            background-color: #343a40 !important;
            border-color: #4b5157 !important;
        }
        body.dark-mode .main-header.navbar .nav-link,
        body.dark-mode .main-header.navbar .nav-link i {
            color: #e9ecef !important;
        }
    </style>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Base Stylesheets (depends on Laravel asset bundling tool) --}}
    @if(config('adminlte.enabled_laravel_mix', false))
        <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_css_path', 'css/app.css')) }}">
            @break

            @case('vite')
                @vite([config('adminlte.laravel_css_path', 'resources/css/app.css'), config('adminlte.laravel_js_path', 'resources/js/app.js')])
            @break

            @case('vite_js_only')
                @vite(config('adminlte.laravel_js_path', 'resources/js/app.js'))
            @break

            @default
                <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
                <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
                <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">

                @if(config('adminlte.google_fonts.allowed', true))
                    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
                @endif
        @endswitch
    @endif

    {{-- Extra Configured Plugins Stylesheets --}}
    @include('adminlte::plugins', ['type' => 'css'])

    {{-- Livewire Styles --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireStyles
        @else
            <livewire:styles />
        @endif
    @endif

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')

    {{-- Favicon (ShulePRO brand — SVG, with a PNG fallback for older browsers/iOS) --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/shulepro-icon.svg') }}">
    <link rel="alternate icon" href="{{ asset('vendor/adminlte/dist/img/shulepro-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('vendor/adminlte/dist/img/shulepro-icon.png') }}">
    <meta name="theme-color" content="#0f2942">

</head>

<body class="@yield('classes_body')" @yield('body_data')>

    {{-- Apply saved dark-mode preference before anything paints, to avoid a flash of the wrong theme --}}
    <script>
        (function () {
            try {
                if (localStorage.getItem('shulepro-dark-mode') === '1') {
                    document.body.classList.add('dark-mode');
                }
            } catch (e) {}
        })();
    </script>

    {{-- Body Content --}}
    @yield('body')

    {{-- Base Scripts (depends on Laravel asset bundling tool) --}}
    @if(config('adminlte.enabled_laravel_mix', false))
        <script src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <script src="{{ mix(config('adminlte.laravel_js_path', 'js/app.js')) }}"></script>
            @break

            @case('vite')
            @case('vite_js_only')
            @break

            @default
                <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
                <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
                <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
                <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
        @endswitch
    @endif

    {{-- Extra Configured Plugins Scripts --}}
    @include('adminlte::plugins', ['type' => 'js'])

    {{-- Livewire Script --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireScripts
        @else
            <livewire:scripts />
        @endif
    @endif

    {{-- Custom Scripts --}}
    @yield('adminlte_js')

</body>

</html>
