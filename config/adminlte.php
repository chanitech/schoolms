<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'MEMA',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>MEMA</b>SMS',
    'logo_img' => 'vendor/adminlte/dist/img/MEMA.webp',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Admin Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/MEMA.webp',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/MEMA.webp',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => true,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => true,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

   'menu' => [
    // Dashboard
    [
        'text' => 'Dashboard',
        'url'  => 'dashboard',
        'icon' => 'fas fa-tachometer-alt',
    ],

    // Students Management
    [
        'text' => 'Students Management',
        'icon' => 'fas fa-user-graduate',
        'submenu' => [
            ['text' => 'Students', 'url' => 'students', 'icon' => 'fas fa-user-graduate'],
            ['text' => 'Guardians', 'url' => 'guardians', 'icon' => 'fas fa-users'],
            //['text' => 'Attendance', 'url' => 'attendance', 'icon' => 'fas fa-calendar-check'],
            ['text' => 'Enrollments', 'url' => 'enrollments', 'icon' => 'fas fa-clipboard-list'],
        ],
    ],

    // Academic Management
    [
        'text' => 'Academic',
        'icon' => 'fas fa-book',
        'submenu' => [
            ['text' => 'Classes', 'url' => 'classes', 'icon' => 'fas fa-chalkboard'],
            ['text' => 'Dormitories', 'url' => 'dormitories', 'icon' => 'fas fa-building'],
            ['text' => 'Academic Sessions', 'url' => 'sessions', 'icon' => 'fas fa-calendar-alt'],
            ['text' => 'Subjects', 'url' => 'subjects', 'icon' => 'fas fa-book-open'],
            ['text' => 'Exams', 'url' => 'exams', 'icon' => 'fas fa-file-alt'],
            ['text' => 'Marks', 'url' => 'marks', 'icon' => 'fas fa-pen'],
            ['text' => 'Divisions', 'url' => 'divisions', 'icon' => 'fas fa-object-group'],
            ['text' => 'Grading & GPA', 'url' => 'grades', 'icon' => 'fas fa-star'],
            ['text' => 'Student Results', 'url' => 'results', 'icon' => 'fas fa-chart-bar'],
            ['text' => 'Class Results', 'url' => '/results/class', 'icon' => 'fas fa-chart-bar'],
        ],
    ],

    // HR & Staff Management
    [
        'text' => 'HR & Staff',
        'icon' => 'fas fa-user-tie',
        'submenu' => [
            ['text' => 'Staff', 'url' => 'staff', 'icon' => 'fas fa-users-cog'],
            ['text' => 'Departments', 'url' => 'departments', 'icon' => 'fas fa-sitemap'], 
            ['text' => 'Job Cards', 'url' => 'jobcards', 'icon' => 'fas fa-briefcase'],
            ['text' => 'my Job Cards', 'url' => 'jobcards/my', 'icon' => 'fas fa-clipboard-list'],
            ['text' => 'Attendance', 'url' => 'attendance', 'icon' => 'fas fa-calendar-check'],
            ['text' => 'Leaves', 'url' => 'leaves', 'icon' => 'fas fa-file-signature'],
            ['text' => 'Received Leaves', 'url' => 'leaves/received', 'icon' => 'fas fa-inbox'],
            ['text' => 'Events', 'url' => 'events', 'icon' => 'fas fa-calendar-alt'],
            //['text' => 'HR Reports', 'url' => 'hr-reports', 'icon' => 'fas fa-chart-line'],
        ],
    ],




    [
    'text' => 'HR Reports',
    'icon' => 'fas fa-chart-line',
    'submenu' => [
        //['text' => 'Summary Dashboard', 'url' => 'hr-reports/summary', 'icon' => 'fas fa-tachometer-alt'],
        ['text' => 'Staff Report', 'url' => 'hr-reports/staff', 'icon' => 'fas fa-users'],
        ['text' => 'Attendance Report', 'url' => 'hr-reports/attendance', 'icon' => 'fas fa-calendar-check'],
        ['text' => 'Leave Report', 'url' => 'hr-reports/leaves', 'icon' => 'fas fa-plane-departure'],
        ['text' => 'Job Cards Report', 'url' => 'hr-reports/jobcards', 'icon' => 'fas fa-briefcase'],
        ['text' => 'Evaluation Report', 'url' => 'hr-reports/evaluation', 'icon' => 'fas fa-star'],
    ]
],




    // Finance & Fees
    [
        'text' => 'Finance & Fees',
        'icon' => 'fas fa-wallet',
        'submenu' => [
            ['text' => 'Invoices', 'url' => 'fees', 'icon' => 'fas fa-file-invoice-dollar'],
            ['text' => 'Payments', 'url' => 'payments', 'icon' => 'fas fa-credit-card'],
            ['text' => 'Fee Reports', 'url' => 'fee-reports', 'icon' => 'fas fa-chart-line'],
        ],
    ],

    

    [
    'text' => 'Library',
    'icon' => 'fas fa-book',
    //'can' => 'library.view',
    'submenu' => [
        [
            'text' => 'Books',
            'url' => 'library/books', // ❌ route() removed
            'icon' => 'fas fa-book',
            //'can' => 'library.view',
        ],
        [
            'text' => 'Categories',
            'url' => 'library/categories',
            'icon' => 'fas fa-tags',
            //'can' => 'library.view',
        ],
        [
            'text' => 'Lending & Returns',
            'url' => 'library/lendings',
            'icon' => 'fas fa-exchange-alt',
            //'can' => 'library.view',
        ],
    ],
],



    // Reports
    [
        'text' => 'Reports',
        'icon' => 'fas fa-chart-bar',
        'submenu' => [
            ['text' => 'Student Reports', 'url' => 'reports/students', 'icon' => 'fas fa-user-graduate'],
            ['text' => 'Staff Reports', 'url' => 'reports/staff', 'icon' => 'fas fa-users-cog'],
            ['text' => 'Finance Reports', 'url' => 'reports/finance', 'icon' => 'fas fa-wallet'],
            ['text' => 'Library Reports', 'url' => 'reports/library', 'icon' => 'fas fa-book'],
        ],
    ],


    // System Settings
[
    'text' => 'System Settings',
    'icon' => 'fas fa-cogs',
    'submenu' => [
        [
            'text' => 'Profile',
            'url'  => '/profile',
            'icon' => 'fas fa-user-cog',
        ],
        [
            'text' => 'School Info',
            'url'  => '/school-info',
            'icon' => 'fas fa-school',
        ],
        
        [
            'text' => 'Roles & Permissions',
            'icon' => 'fas fa-user-shield',
            'submenu' => [
                [
                    'text' => 'Roles',
                    'url'  => '/settings/roles',
                    'icon' => 'fas fa-user-shield',
                ],
                [
                    'text' => 'Permissions',
                    'url'  => '/settings/permissions',
                    'icon' => 'fas fa-key',
                ],
            ],
        ],
       
    ],
],

    

   
],



    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
