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

    // Password Hashing & Encryption Engine
    // Built on modern, lightweight, hardware-hardened cryptographic standards.
    'hash' => [
        // Primary algorithm: 'argon2id', 'argon2i', 'bcrypt', or 'default'
        // Argon2id is the gold standard (memory-hard, GPU/ASIC cracking resistant)
        'algorithm' => env('AUTH_HASH_ALGO', 'argon2id'),

        // RFC 9106 recommended parameters for Argon2id / Argon2i (lightweight & secure)
        'argon2id' => [
            'memory_cost' => (int) env('AUTH_ARGON_MEMORY', 65536), // 64 MB
            'time_cost'   => (int) env('AUTH_ARGON_TIME', 3),       // 3 iterations
            'threads'     => (int) env('AUTH_ARGON_THREADS', 1),     // 1 execution thread
        ],

        // Bcrypt configuration (used as fallback or when explicitly set)
        'bcrypt' => [
            'cost' => (int) env('AUTH_BCRYPT_COST', 12),
        ],

        // Application Pepper (Keyed Hashing Salt)
        // Defends against database breaches by adding an external secret key via HMAC-SHA256.
        // If left empty, hashes are unpeppered standard hashes.
        'pepper' => env('AUTH_PEPPER', ''),

        // Automatic zero-downtime hash upgrade upon successful login
        // Seamlessly upgrades legacy Bcrypt hashes to modern Argon2id hashes
        'auto_rehash' => (bool) env('AUTH_AUTO_REHASH', true),
    ],

    // Named route to redirect unauthenticated guests
    'login_route' => 'auth.login',

    // Fallback login URL if named route is not registered
    'login_url' => '/login',

    // Named route to redirect authenticated users when accessing guest pages
    'home_route' => 'home',

    // Fallback home URL
    'home_url' => '/',
];

