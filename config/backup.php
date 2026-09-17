<?php

return [
    // Full path to the mysqldump binary. Differs per environment (Homebrew
    // MySQL on macOS vs cPanel's ea-mysql on the production server) — set
    // MYSQLDUMP_PATH in .env if 'mysqldump' isn't on the web server's PATH.
    'mysqldump_path' => env('MYSQLDUMP_PATH', 'mysqldump'),

    // Where backup files are written, relative to the 'local' disk root
    // (storage/app). Kept outside public/ so backups are never web-accessible.
    'directory' => 'backups',
];
