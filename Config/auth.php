<?php

/**
 * ============================================================================
 * Srin Engine - Authentication Configuration
 * ============================================================================
 * Configures the native Auth system and identity models.
 * Dynamically resolves settings via env().
 */

return [
    // The Active Record model representing user accounts
    'model' => env('AUTH_MODEL', \Model\Account::class),

    // Session storage key for authenticated identity
    'session_key' => '_srin_auth_id',

    // Primary unique identifier column ('email', 'username', 'phone')
    'identifier' => env('AUTH_IDENTIFIER', 'email'),

    // Password hash column in database
    'password_column' => 'password',

    // Named route to redirect unauthenticated guests
    'login_route' => 'auth.login',

    // Fallback login URL if named route is not registered
    'login_url' => '/login',

    // Named route to redirect authenticated users when accessing guest pages
    'home_route' => 'home',

    // Fallback home URL
    'home_url' => '/',
];
