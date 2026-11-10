<?php

/**
 * ============================================================================
 * Srin Engine - Main Application Configuration
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 *
 * This is the PRIMARY configuration file for your application.
 * Values are dynamically resolved from your .env file via env().
 *
 * Access any value via:
 *   config('name')         or config('app.name')
 *   config('developer')    or config('app.developer')
 *   config('debug')        or config('app.debug')
 */

return [
    // Application Name
    'name' => env('APP_NAME', 'Srin Engine'),

    // Framework Version
    'version' => env('APP_VERSION', '1.2.0'),

    // Lead Developer / Architect
    'developer' => 'Arindam Makar',

    // GitHub Profile
    'github' => 'https://github.com/deadxfire',

    // Application Environment: 'development' or 'production'
    'env' => env('APP_ENV', 'production'),

    // Debug Mode: true displays verbose errors; false renders clean 500 pages (fail-closed for security)
    'debug' => (bool) env('APP_DEBUG', false),

    // Application Encryption Key (used by Core\Crypt for AES-256-GCM encryption & optional peppers)
    'key' => env('APP_KEY', ''),

    // Base Application URL (for reverse proxies or CDNs, configure APP_BASE_URL to canonical domain)
    'base_url' => env('APP_BASE_URL', '/'),

    // Comma-separated list or array of trusted reverse proxy IP addresses
    'trusted_proxies' => env('APP_TRUSTED_PROXIES', ''),

    // Default Application Timezone
    'timezone' => env('APP_TIMEZONE', 'Asia/Kolkata'),

    // Default Locale & Charset
    'locale' => env('APP_LOCALE', 'en'),
    'charset' => 'UTF-8',
];
