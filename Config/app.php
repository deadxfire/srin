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
    'version' => env('APP_VERSION', '1.1.1'),

    // Lead Developer / Architect
    'developer' => 'Arindam Makar',

    // GitHub Profile
    'github' => 'https://github.com/deadxfire',

    // Application Environment: 'development' or 'production'
    'env' => env('APP_ENV', 'development'),

    // Debug Mode: true displays verbose errors; false renders clean 500 pages
    'debug' => (bool) env('APP_DEBUG', true),

    // Base Application URL
    'base_url' => env('APP_BASE_URL', '/'),

    // Default Application Timezone
    'timezone' => env('APP_TIMEZONE', 'Asia/Kolkata'),

    // Default Locale & Charset
    'locale' => env('APP_LOCALE', 'en'),
    'charset' => 'UTF-8',
];
