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
     * Cached valid dummy hash for timing-equalized verification on missing users.
     */
    protected static ?string $dummyHash = null;

    /**
     * Get or create a valid dummy hash generated using active algorithm and options.
     */
    protected static function getDummyHash(): string
    {
        if (self::$dummyHash === null) {
            $algo = self::getHashAlgorithm();
            $options = self::getHashOptions($algo);
            self::$dummyHash = password_hash('__srin_dummy_auth_timing_string__', $algo, $options);
        }
        return self::$dummyHash;
    }

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

        if (headers_sent()) {
            throw new \RuntimeException("Cannot regenerate session ID during login: HTTP headers have already been sent.");
        }

        session_regenerate_id(true);

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

        $query = method_exists($modelClass, 'query') ? $modelClass::query() : new QueryBuilder($modelClass::getTable(), $modelClass);

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
            // Timing equalization against user enumeration: perform dummy hash verification
            self::verify($plainPassword ?? '', self::getDummyHash());
            return false;
        }

        $storedHash = (string) ($user->{$passwordCol} ?? '');

        if (self::verify($plainPassword, $storedHash)) {
            // Automatic zero-downtime password hash upgrade (e.g. from Bcrypt to Argon2id or when cost/pepper changes)
            $autoRehash = function_exists('config') ? (bool) config('auth.hash.auto_rehash', true) : true;
            if ($autoRehash) {
                $hasPepper = self::getPepper() !== '';
                $needsRehash = self::needsRehash($storedHash) || ($hasPepper && !password_verify(self::pepperPassword($plainPassword), $storedHash));

                if ($needsRehash) {
                    $newHash = self::hash($plainPassword);
                    $user->{$passwordCol} = $newHash;
                    if (method_exists($user, 'save')) {
                        try {
                            $user->save();
                        } catch (\Throwable $e) {
                            if (function_exists('log_warning')) {
                                log_warning('Failed to auto-upgrade password hash: ' . $e->getMessage());
                            }
                        }
                    }
                }
            }

            self::login($user);
            return true;
        }

        return false;
    }

    /**
     * Log the user out of the application.
     * Regenerates session ID, invalidates CSRF token, and purges session data.
     */
    public static function logout(): void
    {
        self::startSession();

        $sessionKey = self::getSessionKey();
        unset($_SESSION[$sessionKey]);
        self::$cachedUser = null;

        // Rotate and invalidate CSRF token & clear session
        Security::regenerateCsrfToken();
        $_SESSION = [];

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
     * Resolve the configured password hashing algorithm.
     * Defaults to PASSWORD_ARGON2ID when supported, or PASSWORD_BCRYPT.
     */
    public static function getHashAlgorithm(): string
    {
        $algo = function_exists('config') ? (string) config('auth.hash.algorithm', 'argon2id') : 'argon2id';
        $algo = strtolower(trim($algo));

        if ($algo === 'default') {
            return PASSWORD_DEFAULT;
        }
        if (($algo === 'argon2id' || $algo === 'argon') && defined('PASSWORD_ARGON2ID')) {
            return PASSWORD_ARGON2ID;
        }
        if ($algo === 'argon2i' && defined('PASSWORD_ARGON2I')) {
            return PASSWORD_ARGON2I;
        }
        if ($algo === 'bcrypt' && defined('PASSWORD_BCRYPT')) {
            return PASSWORD_BCRYPT;
        }

        return defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    }

    /**
     * Resolve options for the configured hashing algorithm.
     */
    public static function getHashOptions(?string $algo = null): array
    {
        $algo = $algo ?? self::getHashAlgorithm();

        if ($algo === (defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : '') ||
            $algo === (defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : '')) {
            $memory = function_exists('config') ? (int) config('auth.hash.argon2id.memory_cost', 65536) : 65536;
            $time = function_exists('config') ? (int) config('auth.hash.argon2id.time_cost', 3) : 3;
            $threads = function_exists('config') ? (int) config('auth.hash.argon2id.threads', 1) : 1;

            return [
                'memory_cost' => $memory > 0 ? $memory : 65536,
                'time_cost'   => $time > 0 ? $time : 3,
                'threads'     => $threads > 0 ? $threads : 1,
            ];
        }

        if ($algo === (defined('PASSWORD_BCRYPT') ? PASSWORD_BCRYPT : '')) {
            $cost = function_exists('config') ? (int) config('auth.hash.bcrypt.cost', 12) : 12;
            return ['cost' => max(4, min(31, $cost))];
        }

        return [];
    }

    /**
     * Retrieve the configured application pepper (keyed hashing secret).
     */
    public static function getPepper(): string
    {
        if (function_exists('config')) {
            $pepper = (string) config('auth.hash.pepper', '');
            if ($pepper !== '') {
                return $pepper;
            }
        }
        return '';
    }

    /**
     * Apply the application pepper to a plain-text password using HMAC-SHA256.
     * Prevents offline brute-force attacks even if the database is leaked.
     */
    public static function pepperPassword(string $password): string
    {
        $pepper = self::getPepper();
        if ($pepper === '') {
            return $password;
        }
        return hash_hmac('sha256', $password, $pepper);
    }

    /**
     * Hash a plain-text password using the configured powerful algorithm (Argon2id by default).
     *
     * @param string      $password Plain-text password to hash
     * @param string|null $algo     Optional algorithm override
     * @param array       $options  Optional algorithm options override
     * @return string Cryptographic hash string
     */
    public static function hash(string $password, ?string $algo = null, array $options = []): string
    {
        $algorithm = $algo ?? self::getHashAlgorithm();
        $opts = !empty($options) ? $options : self::getHashOptions($algorithm);
        $prepared = self::pepperPassword($password);

        return password_hash($prepared, $algorithm, $opts);
    }

    /**
     * Verify a plain-text password against a stored hash with backward compatibility.
     *
     * @param string $password Plain-text password
     * @param string $hash     Stored hash string
     * @return bool True if valid, false otherwise
     */
    public static function verify(string $password, string $hash): bool
    {
        if ($hash === '') {
            return false;
        }

        // 1. Check with configured pepper
        $peppered = self::pepperPassword($password);
        if (password_verify($peppered, $hash)) {
            return true;
        }

        // 2. Fallback check without pepper (backwards compatibility for existing users)
        if ($peppered !== $password && password_verify($password, $hash)) {
            return true;
        }

        return false;
    }

    /**
     * Check if a stored hash needs to be re-hashed to match modern algorithm or cost.
     *
     * @param string $hash Stored hash string
     * @return bool True if rehash needed
     */
    public static function needsRehash(string $hash): bool
    {
        if ($hash === '') {
            return true;
        }

        $algo = self::getHashAlgorithm();
        $opts = self::getHashOptions($algo);

        return password_needs_rehash($hash, $algo, $opts);
    }
}

