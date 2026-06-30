<?php

return [
    /*
    |--------------------------------------------------------------------------
    | App domain (without leading dot)
    |--------------------------------------------------------------------------
    | Used for subdomain-based tenant resolution.
    | e.g. "chanitech.co.tz"  →  kitungwa.chanitech.co.tz resolves to slug "kitungwa"
    | Leave empty in local dev — the middleware falls back to default_school_id.
    */
    'domain' => env('TENANT_DOMAIN', ''),

    /*
    |--------------------------------------------------------------------------
    | Default school ID (local dev / single-school fallback)
    |--------------------------------------------------------------------------
    | When no subdomain is present (e.g. 127.0.0.1:8000 in local dev),
    | the middleware uses this school ID to resolve the tenant.
    | Set TENANT_SCHOOL_ID=1 in .env for local development.
    */
    'default_school_id' => env('TENANT_SCHOOL_ID', 1),
];
