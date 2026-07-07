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

    'title' => 'SchoolMS',
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
    'use_full_favicon' => true,

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
        'allowed' => false,
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

    'logo' => '<b>School</b>MS',
    'logo_img' => 'images/schoolms-icon.svg',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'SchoolMS Logo',

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
        'enabled' => true,
        'img' => [
            'path' => 'images/schoolms-icon.svg',
            'alt' => 'SchoolMS Logo',
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
            'path' => 'images/schoolms-icon.svg',
            'alt' => 'SchoolMS Preloader Image',
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
    'dashboard_url' => 'dashboard',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => 'profile.edit',
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
    // Super Admin Panel — only visible to super admins
    [
        'text'    => 'Super Admin',
        'icon'    => 'fas fa-crown',
        'topnav'  => false,
        'active'  => ['super/*'],
        'can'     => 'is-super-admin',
        'submenu' => [
            [
                'text'  => 'All Schools',
                'route' => 'super.schools.index',
                'icon'  => 'fas fa-building',
                'can'   => 'is-super-admin',
            ],
            [
                'text'  => 'Add School',
                'route' => 'super.schools.create',
                'icon'  => 'fas fa-plus-circle',
                'can'   => 'is-super-admin',
            ],
            [
                'text'  => 'All Accounts',
                'route' => 'super.accounts.index',
                'icon'  => 'fas fa-users-cog',
                'can'   => 'is-super-admin',
            ],
        ],
    ],
    // AI Tools
    [
        'text' => 'AI Tools',
        'icon' => 'fas fa-robot',
        'can'  => 'view ai insights',
        'submenu' => [
            [
                'text'  => 'AI Insights',
                'route' => 'ai.dashboard',
                'icon'  => 'fas fa-chart-pie',
                'can'   => 'view ai insights',
            ],
            [
                'text'  => 'AI Assistant',
                'route' => 'ai.assistant.index',
                'icon'  => 'fas fa-comments',
                'can'   => 'view ai insights',
            ],
        ],
    ],

    // Students Management
    [
        'text' => 'Students Management',
        'icon' => 'fas fa-user-graduate',
        'can' => ['view students', 'view guardians', 'view enrollments', 'manage promotions'],
        'submenu' => [
            ['text' => 'Students',   'url' => 'students',    'icon' => 'fas fa-user-graduate',  'can' => 'view students'],
            ['text' => 'Guardians',  'url' => 'guardians',   'icon' => 'fas fa-users',          'can' => 'view guardians'],
            ['text' => 'Enrollments','url' => 'enrollments', 'icon' => 'fas fa-clipboard-list', 'can' => 'view enrollments'],
            ['text' => 'Promotion',  'url' => 'promotion',   'icon' => 'fas fa-level-up-alt',   'can' => 'manage promotions'],
        ],
    ],






   

    
[
    'text' => 'Counseling Office',
    'icon' => 'fas fa-user-md',
    // Previously left ungated on the (incorrect) assumption that the menu
    // builder auto-hides a parent once all its children are filtered out —
    // it doesn't, so this showed an empty section to everyone without any
    // of the counseling permissions.
    'can' => ['create counseling intake forms', 'view counseling intake forms',
              'create session reports', 'view session reports',
              'create group sessions', 'view group sessions', 'view classroom guidance',
              'view interest inventories', 'view aptitude tests', 'create aptitude questions',
              'view multiple intelligence', 'view thinking style', 'view learning preferences',
              'view holland code', 'view mbti test', 'view learning profile reports'],
    'submenu' => [

        // ============================
        //  INDIVIDUAL COUNSELING
        // ============================
        [
            'text' => 'Individual Counseling',
            'icon' => 'fas fa-user-edit',
            'can' => ['create counseling intake forms', 'view counseling intake forms',
                      'create session reports', 'view session reports'],
            'submenu' => [
                [
                    'text' => 'New Intake Form',
                    'route' => 'counseling.intake.create',
                    'icon' => 'far fa-circle',
                    'can'  => 'create counseling intake forms',
                ],
                [
                    'text' => 'All Intake Forms',
                    'route' => 'counseling.intake.index',
                    'icon' => 'far fa-circle',
                    'can'  => 'view counseling intake forms',
                ],
                [
                    'text' => 'New Session Report',
                    'route' => 'counseling.individual.create',
                    'icon' => 'far fa-circle',
                    'can'  => 'create session reports',   // placeholder
                ],
                [
                    'text' => 'All Session Reports',
                    'route' => 'counseling.individual.index',
                    'icon' => 'far fa-circle',
                    'can'  => 'view session reports',     // placeholder
                ],
            ],
        ],

        // ============================
        //  GROUP COUNSELING
        // ============================
        [
            'text' => 'Group Counseling',
            'icon' => 'fas fa-users',
            'can' => ['create group sessions', 'view group sessions'],
            'submenu' => [
                [
                    'text' => 'New Group Session',
                    'route' => 'counseling.group.create',
                    'icon' => 'far fa-circle',
                    'can'  => 'create group sessions',    // placeholder
                ],
                [
                    'text' => 'All Group Sessions',
                    'route' => 'counseling.group.index',
                    'icon' => 'far fa-circle',
                    'can'  => 'view group sessions',      // placeholder
                ],
            ],
        ],

        // ============================
        //  CLASSROOM GUIDANCE
        // ============================
        [
            'text' => 'Classroom Guidance',
            'route' => 'classroom-guidances.index',
            'icon' => 'fas fa-chalkboard-teacher',
            'can'  => 'view classroom guidance',          // placeholder
        ],

        // ============================
        //  LEARNING PROFILE
        // ============================
        [
            'text' => 'Learning Profile',
            'icon' => 'fas fa-brain',
            'can' => ['view interest inventories', 'view aptitude tests', 'create aptitude questions',
                      'view multiple intelligence', 'view thinking style', 'view learning preferences',
                      'view holland code', 'view mbti test', 'view learning profile reports'],
            'submenu' => [
                [
                    'text' => 'Interest Inventory',
                    'route' => 'interest-inventories.index',
                    'icon' => 'far fa-circle',
                    'can'  => 'view interest inventories', // placeholder
                ],
                [
                    'text' => 'Aptitude Test',
                    'icon' => 'far fa-file-alt',
                    'route' => 'counseling.psychometric.aptitude.index',
                    'can'  => 'view aptitude tests',       // placeholder
                ],
                [
                    'text' => 'Aptitude Test Questions',
                    'icon' => 'far fa-circle',
                    'route' => 'aptitude.questions.create',
                    'can'  => 'create aptitude questions', // placeholder
                ],
                [
                    'text' => 'Multiple Intelligence',
                    'icon' => 'far fa-circle',
                    'url'  => '#',
                    'can'  => 'view multiple intelligence', // placeholder
                ],
                [
                    'text' => 'Thinking Style (Gregorc)',
                    'icon' => 'far fa-circle',
                    'url'  => '#',
                    'can'  => 'view thinking style',       // placeholder
                ],
                [
                    'text' => 'Learning Preferences & Styles',
                    'icon' => 'far fa-circle',
                    'url'  => '#',
                    'can'  => 'view learning preferences',  // placeholder
                ],
                [
                    'text' => 'Holland Code (RIASEC)',
                    'icon' => 'far fa-circle',
                    'url'  => '#',
                    'can'  => 'view holland code',         // placeholder
                ],
                [
                    'text' => 'MBTI Test',
                    'icon' => 'far fa-circle',
                    'url'  => '#',
                    'can'  => 'view mbti test',            // placeholder
                ],
                [
                    'text' => 'Learning Profile Report',
                    'icon' => 'far fa-circle',
                    'url'  => '#',
                    'can'  => 'view learning profile reports', // placeholder
                ],
            ],
        ],
    ],
],




    

    // Academic Management
    [
        'text' => 'Academic',
        'icon' => 'fas fa-book',
        'can' => ['view sessions', 'view classes', 'view divisions', 'view dormitories',
                  'view subjects', 'view subject assignments', 'view teacher assignments',
                  'view timetable', 'view exams', 'view marks', 'enter marks', 'view grading',
                  'view results', 'export marksheets'],
        'submenu' => [

            // ── School Setup ──────────────────────────────────────────
            [
                'text'    => 'School Setup',
                'icon'    => 'fas fa-school',
                'can'     => ['view sessions', 'view classes', 'view divisions', 'view dormitories'],
                'submenu' => [
                    ['text' => 'Academic Sessions', 'url' => 'sessions',    'icon' => 'fas fa-calendar-alt',   'can' => 'view sessions'],
                    ['text' => 'Classes',           'url' => 'classes',     'icon' => 'fas fa-chalkboard',     'can' => 'view classes'],
                    ['text' => 'Divisions',         'url' => 'divisions',   'icon' => 'fas fa-object-group',   'can' => 'view divisions'],
                    ['text' => 'Dormitories',       'url' => 'dormitories', 'icon' => 'fas fa-building',       'can' => 'view dormitories'],
                ],
            ],

            // ── Subjects & Staff ──────────────────────────────────────
            [
                'text'    => 'Subjects & Staff',
                'icon'    => 'fas fa-chalkboard-teacher',
                'can'     => ['view subjects', 'view subject assignments', 'view teacher assignments'],
                'submenu' => [
                    ['text' => 'Subjects',            'url' => 'subjects',           'icon' => 'fas fa-book-open', 'can' => 'view subjects'],
                    // These pointed at URLs with no matching route at all (404, not
                    // 403) — no "assign students/teachers" listing page was ever
                    // built. Assigning students to a subject already happens via
                    // Subjects > a specific subject's "assign students" action.
                    ['text' => 'Subjects Assignment', 'url' => '#', 'icon' => 'fas fa-tasks',     'can' => 'view subject assignments'],
                    ['text' => 'Teachers Assignment', 'url' => '#', 'icon' => 'fas fa-user-tie',  'can' => 'view teacher assignments'],
                ],
            ],

            // ── Timetable & Teaching ──────────────────────────────────
            [
                'text'    => 'Timetable & Teaching',
                'icon'    => 'fas fa-calendar-week',
                'can'     => 'view timetable',
                'submenu' => [
                    [
                        'text'        => 'Timetable',
                        'url'         => 'timetables',
                        'icon'        => 'fas fa-calendar-week',
                        'can'         => 'view timetable',
                        'label'       => 'New',
                        'label_color' => 'info',
                    ],
                    [
                        'text'  => 'My Sessions',
                        'route' => 'timetables.my-sessions',
                        'icon'  => 'fas fa-desktop',
                        'can'   => 'view timetable',
                    ],
                    [
                        'text'  => 'Daily Reports',
                        'route' => 'daily-reports.index',
                        'icon'  => 'fas fa-clipboard-list',
                        'can'   => 'view daily reports',
                    ],
                    [
                        'text'    => 'Lesson Plans',
                        'icon'    => 'fas fa-tasks',
                        'can'     => 'view lesson plans',
                        'submenu' => [
                            ['text' => 'All Lesson Plans',     'url' => 'topic-coverage',            'icon' => 'fas fa-list',       'can' => 'view lesson plans'],
                            ['text' => 'Evaluation & Progress','url' => 'topic-coverage/evaluation', 'icon' => 'fas fa-chart-line', 'can' => 'view lesson plans'],
                        ],
                    ],
                    [
                        'text'  => 'Period Settings',
                        'route' => 'timetable-periods.index',
                        'icon'  => 'fas fa-clock',
                        'can'   => 'is-timetable-admin',
                    ],
                ],
            ],

            // ── Assessment ────────────────────────────────────────────
            [
                'text'    => 'Assessment',
                'icon'    => 'fas fa-file-alt',
                'can'     => ['view exams', 'view marks', 'enter marks', 'view grading'],
                'submenu' => [
                    ['text' => 'Exams',              'url' => 'exams',                    'icon' => 'fas fa-file-alt',  'can' => 'view exams'],
                    ['text' => 'Marks',              'url' => 'marks',                    'icon' => 'fas fa-pen',       'can' => 'view marks'],
                    ['text' => 'Exam Questions',     'url' => 'exam-questions/manage',    'icon' => 'fas fa-list-ol',   'can' => 'enter marks'],
                    ['text' => 'Question Evaluation','url' => 'marks/question-evaluation','icon' => 'fas fa-chart-bar', 'can' => 'enter marks'],
                    ['text' => 'Grading & GPA',      'url' => 'grades',                  'icon' => 'fas fa-star',      'can' => 'view grading'],
                ],
            ],

            // ── Results & Reports ─────────────────────────────────────
            [
                'text'    => 'Results & Reports',
                'icon'    => 'fas fa-chart-bar',
                'can'     => ['view results', 'export marksheets'],
                'submenu' => [
                    ['text' => 'Student Results', 'url'   => 'results',       'icon' => 'fas fa-chart-bar', 'can' => 'view results'],
                    ['text' => 'Class Results',   'url'   => 'results/class', 'icon' => 'fas fa-chart-bar', 'can' => 'view results'],
                    ['text' => 'Export Marksheet','route' => 'results.export.form', 'icon' => 'fas fa-file-pdf', 'can' => 'export marksheets'],
                ],
            ],

        ],
    ],



    




    // HR & Staff Management
    [
    'text' => 'HR & Staff',
    'icon' => 'fas fa-user-tie',
    // Previously assumed the menu auto-hides once all children are
    // filtered — it doesn't (see Counseling Office above), so this showed
    // an empty section to anyone without any HR permission.
    'can' => ['view department dashboard', 'view staff', 'view departments',
              'view any jobcards', 'view own jobcards', 'view attendance', 'view leaves',
              'create leaves', 'view own leaves',
              'view events', 'view staff report',
              'view attendance report', 'view leave report', 'view job cards report',
              'view evaluation report'],
    'submenu' => [
        // HOD Department Dashboard
        [
            'text'  => 'My Department',
            'route' => 'hod.dashboard',
            'icon'  => 'fas fa-chalkboard-teacher',
            'can'   => 'view department dashboard',
        ],
        [
            'text'  => 'Staff Reports',
            'route' => 'daily-reports.hod',
            'icon'  => 'fas fa-clipboard-check',
            'can'   => 'view department dashboard',
        ],

        // Staff list – requires 'view staff' (used in StaffController@index)
        ['text' => 'Staff', 'url' => 'staff', 'icon' => 'fas fa-users-cog', 'can' => 'view staff'],

        // Departments – assumes a DepartmentController with similar permissions
        ['text' => 'Departments', 'url' => 'departments', 'icon' => 'fas fa-sitemap', 'can' => 'view departments'],

        // Job Cards (global list) – JobCardController actually requires
        // 'view any jobcards' (no space) — this used to say 'view job
        // cards' (an older, differently-named permission), so anyone with
        // only that legacy permission would see the link and 403 on click.
        ['text' => 'Job Cards', 'url' => 'jobcards', 'icon' => 'fas fa-briefcase', 'can' => 'view any jobcards'],

        // My Job Cards – same fix: real permission is 'view own jobcards' (no space).
        ['text' => 'my Job Cards', 'url' => 'jobcards/my', 'icon' => 'fas fa-clipboard-list', 'can' => 'view own jobcards'],

        // Attendance – requires 'view attendance'
        ['text' => 'Attendance', 'url' => 'attendance', 'icon' => 'fas fa-calendar-check', 'can' => 'view attendance'],

        // Leaves – index() shows "my own leaves" for any staff member and
        // isn't actually gated behind 'view leaves' (that's the approver-
        // side permission, used by 'Received Leaves' below). Match either.
        ['text' => 'Leaves', 'url' => 'leaves', 'icon' => 'fas fa-file-signature',
         'can' => ['create leaves', 'view own leaves', 'view leaves']],

        // Received Leaves – LeaveController@received is actually gated by
        // 'view leaves' (same as the index above), not a separate
        // 'view received leaves' permission.
        ['text' => 'Received Leaves', 'url' => 'leaves/received', 'icon' => 'fas fa-inbox', 'can' => 'view leaves'],

        // Events – requires 'view events'
        ['text' => 'Events', 'url' => 'events', 'icon' => 'fas fa-calendar-alt', 'can' => 'view events'],

        // HR Reports parent – will be shown if any sub-report is visible
        [
            'text' => 'HR Reports',
            'icon' => 'fas fa-chart-line',
            'can' => ['view staff report', 'view attendance report', 'view leave report',
                      'view job cards report', 'view evaluation report'],
            'submenu' => [
                ['text' => 'Staff Report', 'url' => 'hr-reports/staff', 'icon' => 'fas fa-users', 'can' => 'view staff report'],
                ['text' => 'Attendance Report', 'url' => 'hr-reports/attendance', 'icon' => 'fas fa-calendar-check', 'can' => 'view attendance report'],
                ['text' => 'Leave Report', 'url' => 'hr-reports/leaves', 'icon' => 'fas fa-plane-departure', 'can' => 'view leave report'],
                ['text' => 'Job Cards Report', 'url' => 'hr-reports/jobcards', 'icon' => 'fas fa-briefcase', 'can' => 'view job cards report'],
                ['text' => 'Evaluation Report', 'url' => 'hr-reports/evaluation', 'icon' => 'fas fa-star', 'can' => 'view evaluation report'],
            ],
        ],
    ],
],



[
    'text' => 'Treasurer Office',
    'icon' => 'fas fa-university',
    'submenu' => [

        // ========== LOAN MANAGEMENT ==========
        [
            'text' => 'Loan Management',
            'icon' => 'fas fa-hand-holding-usd',
            'submenu' => [
                [
                    'text' => 'Apply for Loan',
                    'route' => 'staff.loans.create',
                    'icon' => 'fas fa-pen-alt',
                ],
                [
                    'text' => 'My Loans',
                    'route' => 'staff.loans.index',
                    'icon' => 'fas fa-list-ul',
                ],
                [
                    'text' => 'Bank Statements (My)',
                    'route' => 'staff.bank-statements.index',
                    'icon' => 'fas fa-university',
                ],
                // Treasurer‑only actions
                [
                    'text' => 'Loan Categories (Setup)',
                    'route' => 'treasurer.loan-categories.index',
                    'icon' => 'fas fa-tags',
                    'can' => 'is-treasurer',
                ],
                [
                    'text' => 'Pending Loan Approvals',
                    'route' => 'treasurer.loans.pending',
                    'icon' => 'fas fa-clock',
                    'can' => 'approve loans',
                    'label' => $pendingLoansCount ?? 0,
                    'label_color' => 'warning',
                ],
                [
                    'text' => 'All Loan Applications',
                    'route' => 'treasurer.loans.index',
                    'icon' => 'fas fa-database',
                    'can' => 'is-loan-approver',
                ],
                [
                    'text' => 'Record Repayments',                     // ✅ NEW
                    'route' => 'treasurer.loans.active',               // ✅ route defined in web.php
                    'icon' => 'fas fa-money-bill-wave',
                    'can' => 'is-loan-approver',
                ],
                [
                    'text' => 'Upload Bank Statements',
                    'route' => 'treasurer.bank-statements.create',
                    'icon' => 'fas fa-upload',
                    'can' => 'is-loan-approver',
                ],
            ],
        ],

        // ========== BUDGETS ==========
        [
            'text' => 'Budgets',
            'icon' => 'fas fa-file-invoice',
            'can' => ['create budgets', 'view pending approvals', 'view budgets', 'view hod dashboard'],
            'submenu' => [
                [
                    'text' => 'Submit Budget',
                    'route' => 'finance.budgets.create',
                    'icon' => 'fas fa-plus',
                    'can' => 'create budgets',
                ],
                [
                    'text' => 'Pending Approvals',
                    'route' => 'finance.budgets.pending',
                    'icon' => 'fas fa-check-circle',
                    'can' => 'view pending approvals',
                    'label' => $pendingBudgetsCount ?? 0,
                    'label_color' => 'warning',
                ],
                [
                    'text' => 'All Budgets',
                    'route' => 'finance.budgets.index',
                    'icon' => 'fas fa-list',
                    'can' => 'view budgets',
                ],
                [
                    'text' => 'HOD Dashboard',
                    'route' => 'finance.budgets.hod',
                    'icon' => 'fas fa-user-tie',
                    'can' => 'view hod dashboard',
                ],
            ],
        ],

        // ========== INVOICES ==========
        [
            'text' => 'Invoices',
            'icon' => 'fas fa-receipt',
            'can' => ['approve invoices', 'pay invoices', 'view invoices'],
            'submenu' => [
                [
                    'text' => 'DO Approvals',
                    'route' => 'finance.invoices.do',
                    'icon' => 'fas fa-check-double',
                    'can' => 'approve invoices',
                    'label' => $pendingInvoicesCount ?? 0,
                    'label_color' => 'warning',
                ],
                [
                    'text' => 'Finance Dashboard',
                    'route' => 'finance.invoices.finance',
                    'icon' => 'fas fa-credit-card',
                    'can' => 'pay invoices',
                ],
                [
                    'text' => 'All Invoices',
                    'route' => 'finance.invoices.index',
                    'icon' => 'fas fa-list-alt',
                    'can' => 'view invoices',
                ],
            ],
        ],

        // ========== FEES & PAYMENTS ==========
        [
            'text' => 'Fees & Payments',
            'icon' => 'fas fa-wallet',
            'can' => ['view bills', 'view student bills', 'view payments', 'verify payments', 'view pocket money'],
            'submenu' => [
                [
                    'text' => 'Class Bills',
                    'route' => 'finance.bills.index',
                    'icon' => 'fas fa-file-invoice-dollar',
                    'can' => 'view bills',
                ],
                [
                    'text' => 'Student Bills',
                    'route' => 'finance.student_bills.index',
                    'icon' => 'fas fa-file-alt',
                    'can' => 'view student bills',
                ],
                [
                    'text' => 'Payments',
                    'route' => 'finance.payments.index',
                    'icon' => 'fas fa-credit-card',
                    'can' => 'view payments',
                ],
                [
                    'text' => 'Verify/Flag Payments',
                    'route' => 'finance.payments.review',
                    'icon' => 'fas fa-check-double',
                    'can' => 'verify payments',
                ],
                // ✅ FIXED: Use direct URL instead of route name
                [
                    'text' => 'Pocket Money',
                     'url' => '/finance/pocket/transactions',   // ✅ correct
                    'icon' => 'fas fa-piggy-bank',
                    'can' => 'view pocket money',
                ],
            ],
        ],

        // ========== PROCUREMENT ==========
        [
            'text' => 'Procurement',
            'icon' => 'fas fa-shopping-cart',
            'can' => ['create procurement requests', 'approve procurement requests', 'disburse payments',
                      'create stock requests', 'review stock requests'],
            'submenu' => [
                [
                    'text' => 'Requests',
                    'route' => 'treasurer.procurement.index',
                    'icon' => 'fas fa-list',
                    // Cashier can't create/approve but still needs to see this
                    // list to find approved requests awaiting disbursement.
                    'can' => ['create procurement requests', 'approve procurement requests', 'disburse payments'],
                ],
                [
                    'text' => 'New Request',
                    'route' => 'treasurer.procurement.create',
                    'icon' => 'fas fa-plus',
                    'can' => 'create procurement requests',
                ],
                [
                    'text' => 'Request Stock',
                    'route' => 'treasurer.stock-requests.create',
                    'icon' => 'fas fa-dolly',
                    'can' => 'create stock requests',
                ],
                [
                    'text' => 'Stock Requests',
                    'route' => 'treasurer.stock-requests.index',
                    'icon' => 'fas fa-clipboard-list',
                    'can' => ['create stock requests', 'review stock requests'],
                ],
            ],
        ],

        // ========== INVENTORY ==========
        [
            'text' => 'Inventory',
            'icon' => 'fas fa-boxes',
            'can'  => ['manage settings', 'manage stock', 'view inventory'],
            'submenu' => [
                [
                    'text'  => 'Dashboard',
                    'url'   => 'inventory',
                    'icon'  => 'fas fa-tachometer-alt',
                    'can'   => ['manage settings', 'manage stock', 'view inventory'],
                ],
                [
                    'text'  => 'Items',
                    'url'   => 'inventory/items',
                    'icon'  => 'fas fa-box',
                    'can'   => ['manage settings', 'manage stock', 'view inventory'],
                ],
                [
                    'text'  => 'Categories',
                    'url'   => 'inventory/categories',
                    'icon'  => 'fas fa-tags',
                    'can'   => ['manage settings', 'manage stock', 'view inventory'],
                ],
                [
                    'text'  => 'Transactions',
                    'url'   => 'inventory/transactions',
                    'icon'  => 'fas fa-exchange-alt',
                    'can'   => ['manage settings', 'manage stock', 'view inventory'],
                ],
            ],
        ],

        // ========== PERFORMANCE & TASKS ==========
        // Was completely ungated (parent + My Dashboard + Tasks) — every
        // authenticated user of any role saw this Treasurer/Finance-Office
        // section, including Teacher. My Dashboard and Tasks' own controllers
        // are deliberately self-scoped (no permission gate, see their
        // comments) but now enforce 'can:is-finance-office' at the route
        // level (AppServiceProvider) — this menu 'can' matches that same
        // Gate so nobody sees a link they'd 403 on.
        [
            'text' => 'Performance & Tasks',
            'icon' => 'fas fa-tasks',
            'can' => 'is-finance-office',
            'submenu' => [
                [
                    'text' => 'My Dashboard',
                    'route' => 'treasurer.my-dashboard',
                    'icon' => 'fas fa-user-circle',
                    'can' => 'is-finance-office',
                ],
                [
                    'text' => 'Tasks',
                    'route' => 'treasurer.tasks.index',
                    'icon' => 'fas fa-list-check',
                    'can' => 'is-finance-office',
                ],
                [
                    'text' => 'Job Descriptions',
                    'route' => 'treasurer.job-descriptions.index',
                    'icon' => 'fas fa-id-card',
                    'can' => 'manage job descriptions',
                ],
                [
                    'text' => 'Office Oversight Dashboard',
                    'route' => 'treasurer.dashboard',
                    'icon' => 'fas fa-chart-line',
                    'can' => 'view finance dashboard',
                ],
            ],
        ],
    ],
],

    
[
    'text' => 'Library',
    'icon' => 'fas fa-book',
    'can' => ['view books', 'view categories', 'view lendings'],
    'submenu' => [
        [
            'text' => 'Books',
            'url' => 'library/books',
            'icon' => 'fas fa-book',
            'can'  => 'view books',
        ],
        [
            'text' => 'Categories',
            'url' => 'library/categories',
            'icon' => 'fas fa-tags',
            'can'  => 'view categories',
        ],
        [
            'text' => 'Lending & Returns',
            'url' => 'library/lendings',
            'icon' => 'fas fa-exchange-alt',
            'can'  => 'view lendings',
        ],
    ],
],

    // Document Library
    [
        'text'  => 'Document Library',
        'url'   => 'documents',
        'icon'  => 'fas fa-folder-open',
        'can'   => 'view documents',
    ],

    // Reports
    //[
        //'text' => 'Reports',
        //'icon' => 'fas fa-chart-bar',
        //'submenu' => [
            //['text' => 'Student Reports', 'url' => 'reports/students', 'icon' => 'fas fa-user-graduate'],
            //['text' => 'Staff Reports', 'url' => 'reports/staff', 'icon' => 'fas fa-users-cog'],
            //['text' => 'Finance Reports', 'url' => 'reports/finance', 'icon' => 'fas fa-wallet'],
            //['text' => 'Library Reports', 'url' => 'reports/library', 'icon' => 'fas fa-book'],
     //   ],
    //],


 [
    'text' => 'System Settings',
    'icon' => 'fas fa-cogs',
    'submenu' => [
        [
            'text' => 'Profile',
            'url'  => '/profile',
            'icon' => 'fas fa-user-cog',
            // No 'can' – visible to all authenticated users
        ],
        [
            'text' => 'School Info',
            'url'  => '/settings/school-info',
            'icon' => 'fas fa-school',
            'can'  => 'manage settings',
        ],
        [
            'text' => 'Roles & Permissions',
            'icon' => 'fas fa-user-shield',
            'can'  => 'view roles',
            'submenu' => [
                [
                    'text' => 'Roles',
                    'url'  => '/settings/roles',
                    'icon' => 'fas fa-user-shield',
                    'can'  => 'view roles',
                ],
                [
                    'text' => 'Permissions',
                    'url'  => '/settings/permissions',
                    'icon' => 'fas fa-key',
                    'can'  => 'view permissions',
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