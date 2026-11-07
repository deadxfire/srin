<?php

namespace Core;

use Exception;

/**
 * ============================================================================
 * Srin Engine - Universal Authentication Guard & Identity Manager
 * ============================================================================
 * Developed by Arindam Makar
 * 
 * Provides session-based user authentication, password hashing, credential
 * verification, session fixation protection, and dynamic identity models.
 * Compatible with any model (Account, User, Member, Admin).
 * ============================================================================
 */
class Auth
{
    /**
     * In-memory cache of authenticated identity model for the current request.
     */
    protected static ?object $cachedUser = null;

    /**
     * Configured model class name.
     */
    protected static ?string $modelClass = null;

    /**
     * Ensure session is active.
     */
    protected static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Get or set the identity model class.
     * Defaults to config('auth.model'), or auto-detects Model\Account / Model\User.
     */
    public static function model(): string
    {
        if (self::$modelClass !== null) {
            return self::$modelClass;
        }

        $configured = function_exists('config') ? config('auth.model') : null;
        if (!empty($configured) && class_exists($configured)) {
            self::$modelClass = $configured;
            return self::$modelClass;
        }

        if (class_exists('\\Model\\Account')) {
            self::$modelClass = '\\Model\\Account';
            return self::$modelClass;
        }

        if (class_exists('\\Model\\User')) {
            self::$modelClass = '\\Model\\User';
            return self::$modelClass;
        }

        // Fallback default
        self::$modelClass = '\\Model\\Account';
        return self::$modelClass;
    }

    /**
     * Specify a custom model class for authentication at runtime.
     * Example: Auth::useModel(\Model\Admin::class);
     */
    public static function useModel(string $modelClass): void
    {
        self::$modelClass = $modelClass;
        self::$cachedUser = null;
    }

    /**
     * Get the session key for authentication.
     */
    public static function getSessionKey(): string
    {
        return function_exists('config') ? (string) config('auth.session_key', '_srin_auth_id') : '_srin_auth_id';
    }

    /**
     * Get the currently authenticated user model instance, or null.
     */
    public static function user(): ?object
    {
        self::startSession();

        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $sessionKey = self::getSessionKey();
        $userId = $_SESSION[$sessionKey] ?? null;

        if (!$userId) {
            return null;
        }

        $modelClass = self::model();
        if (!class_exists($modelClass)) {
            return null;
        }

        // Retrieve user via Active Record
        $user = method_exists($modelClass, 'find') ? $modelClass::find($userId) : null;
        if ($user) {
            self::$cachedUser = $user;
            return $user;
        }

        // Session ID refers to deleted record -> clean up
        self::logout();
        return null;
    }

    /**
     * Get the primary key ID of the currently authenticated user.
     */
    public static function id(): ?int
    {
        $user = self::user();
        if (!$user) {
            return null;
        }
        return isset($user->id) ? (int) $user->id : null;
    }

    /**
     * Determine if the current visitor is authenticated.
     */
    public static function check(): bool
    {
        return self::user() !== null;
    }

    /**
     * Determine if the current visitor is a guest (not authenticated).
     */
    public static function guest(): bool
    {
        return !self::check();
    }

    /**
     * Log a user in manually by their model instance.
     * Automatically regenerates session ID to prevent session fixation attacks.
     */
    public static function login(object $user): void
    {
        self::startSession();

        if (!headers_sent()) {
            @session_regenerate_id(true);
        }

        $sessionKey = self::getSessionKey();
        $primaryKey = $user->id ?? null;

        if ($primaryKey !== null) {
            $_SESSION[$sessionKey] = $primaryKey;
        }

        self::$cachedUser = $user;
    }

    /**
     * Attempt to authenticate a user with email/username and password.
     *
     * Example with scalar:
     *   Auth::attempt('user@example.com', 'secret123');
     *
     * Example with criteria array:
     *   Auth::attempt(['email' => $email, 'password' => $pass, 'is_active' => 1]);
     */
    public static function attempt(string|array $credentials, ?string $password = null): bool
    {
        $modelClass = self::model();
        if (!class_exists($modelClass)) {
            return false;
        }

        $query = method_exists($modelClass, 'where') ? $modelClass::where('1', '1') : null;
        if (!$query) {
            return false;
        }

        $passwordCol = function_exists('config') ? (string) config('auth.password_column', 'password') : 'password';
        $plainPassword = $password;

        if (is_array($credentials)) {
            if (isset($credentials['password'])) {
                $plainPassword = (string) $credentials['password'];
                unset($credentials['password']);
            }
            foreach ($credentials as $col => $val) {
                $query->where($col, $val);
            }
        } else {
            $identifierCol = function_exists('config') ? (string) config('auth.identifier', 'email') : 'email';
            $query->where($identifierCol, trim($credentials));
        }

        $user = $query->first();

        if (!$user || $plainPassword === null) {
            return false;
        }

        $storedHash = (string) ($user->{$passwordCol} ?? '');

        if (self::verify($plainPassword, $storedHash)) {
            self::login($user);
            return true;
        }

        return false;
    }

    /**
     * Log the user out of the application.
     * Regenerates session ID and purges session key.
     */
    public static function logout(): void
    {
        self::startSession();

        $sessionKey = self::getSessionKey();
        unset($_SESSION[$sessionKey]);
        self::$cachedUser = null;

        if (!headers_sent()) {
            @session_regenerate_id(true);
        }
    }

    /**
     * Refresh the cached user instance from the database.
     */
    public static function reload(): ?object
    {
        self::$cachedUser = null;
        return self::user();
    }

    /**
     * Hash a plain-text password using modern, secure algorithm.
     */
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify a plain-text password against a stored hash.
     */
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}

