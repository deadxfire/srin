<?php

namespace Core;

use Throwable;
use Closure;
use Exception;

/**
 * ============================================================================
 * Srin Engine - Fast Dynamic Lifecycle Igniter & Service Container
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 *
 * High-performance bootstrapper and dynamic lifecycle engine for Srin.
 * Provides dynamic service binding, lifecycle hooks, middleware execution,
 * and microsecond benchmarking without external overhead.
 */
class Igniter
{
    /**
     * Framework version.
     */
    public const VERSION = '1.2.0';

    /**
     * Boot timestamp in microtime.
     */
    protected static float $startTime = 0.0;

    /**
     * Dynamic service container bindings.
     * @var array<string, mixed>
     */
    protected static array $bindings = [];

    /**
     * Resolved singleton instances.
     * @var array<string, mixed>
     */
    protected static array $singletons = [];

    /**
     * Dynamic lifecycle event hooks.
     * @var array<string, array<callable>>
     */
    protected static array $hooks = [
        'boot'      => [],
        'request'   => [],
        'response'  => [],
        'error'     => [],
        'terminate' => [],
    ];

    /**
     * Dynamic middlewares executed before route dispatch.
     * @var array<callable|string>
     */
    protected static array $middlewares = [];

    /**
     * Dynamic in-memory key-value state store.
     * @var array<string, mixed>
     */
    protected static array $state = [];

    /**
     * Has the framework already booted?
     */
    protected static bool $booted = false;

    /**
     * Dynamic Service Container: Register a transient factory binding.
     * Example: Igniter::bind('mailer', fn() => new CustomMailer());
     */
    public static function bind(string $abstract, mixed $concrete): void
    {
        self::$bindings[$abstract] = $concrete;
        unset(self::$singletons[$abstract]);
    }

    /**
     * Dynamic Service Container: Register a shared singleton.
     * Example: Igniter::singleton('cache', fn() => new RedisStore());
     */
    public static function singleton(string $abstract, mixed $concrete): void
    {
        self::$bindings[$abstract] = $concrete;
        self::$singletons[$abstract] = null; // Marked as singleton, resolved on first make
    }

    /**
     * Dynamic Service Container: Bind an existing object instance.
     */
    public static function instance(string $abstract, object $instance): void
    {
        self::$singletons[$abstract] = $instance;
    }

    /**
     * Dynamic Service Container: Resolve an abstract service.
     */
    public static function make(string $abstract, array $parameters = []): mixed
    {
        // 1. Check existing singleton instance
        if (isset(self::$singletons[$abstract]) && self::$singletons[$abstract] !== null) {
            return self::$singletons[$abstract];
        }

        // 2. Check registered binding
        if (isset(self::$bindings[$abstract])) {
            $concrete = self::$bindings[$abstract];

            if ($concrete instanceof Closure) {
                $object = $concrete(...$parameters);
            } elseif (is_string($concrete) && class_exists($concrete)) {
                $object = new $concrete(...$parameters);
            } else {
                $object = $concrete;
            }

            // Cache if registered as singleton
            if (array_key_exists($abstract, self::$singletons)) {
                self::$singletons[$abstract] = $object;
            }

            return $object;
        }

        // 3. Fallback: Instantiate class directly if exists
        if (class_exists($abstract)) {
            $object = new $abstract(...$parameters);
            return $object;
        }

        throw new Exception("Unable to resolve service [{$abstract}] from Srin Igniter container.");
    }

    /**
     * Check if a service or binding exists in container.
     */
    public static function has(string $abstract): bool
    {
        return isset(self::$bindings[$abstract]) || isset(self::$singletons[$abstract]);
    }

    /**
     * Register a dynamic lifecycle event listener.
     * Available events: 'boot', 'request', 'response', 'error', 'terminate'
     *
     * Example:
     *   Igniter::on('request', function($uri) {
     *       // Log incoming URI or inspect headers
     *   });
     */
    public static function on(string $event, callable $callback): void
    {
        self::$hooks[$event][] = $callback;
    }

    /**
     * Trigger a dynamic lifecycle event hook.
     */
    public static function trigger(string $event, mixed ...$args): void
    {
        if (!empty(self::$hooks[$event])) {
            foreach (self::$hooks[$event] as $hook) {
                $hook(...$args);
            }
        }
    }

    /**
     * Register a dynamic global middleware.
     * Middleware receives ($requestUri, $nextClosure).
     *
     * Example:
     *   Igniter::middleware(function($uri, $next) {
     *       // inspect or modify request
     *       return $next();
     *   });
     */
    public static function middleware(callable|string $middleware): void
    {
        self::$middlewares[] = $middleware;
    }

    /**
     * Dynamic state store: Set a runtime key-value item.
     */
    public static function set(string $key, mixed $value): void
    {
        self::$state[$key] = $value;
    }

    /**
     * Dynamic state store: Retrieve a runtime item.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$state[$key] ?? $default;
    }

    /**
     * Execution time in milliseconds since startup.
     * Example: "1.42 ms"
     */
    public static function elapsed(int $decimals = 2): string
    {
        $start = defined('SRIN_START') ? SRIN_START : (self::$startTime ?: microtime(true));
        $diff = (microtime(true) - $start) * 1000;
        return number_format($diff, $decimals) . ' ms';
    }

    /**
     * Current memory usage formatted as human-readable string.
     * Example: "2.15 MB"
     */
    public static function memory(): string
    {
        $bytes = memory_get_usage(true);
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return number_format($bytes / (1024 ** $power), 2) . ' ' . ($units[$power] ?? 'B');
    }

    /**
     * Peak memory usage formatted as human-readable string.
     */
    public static function peakMemory(): string
    {
        $bytes = memory_get_peak_usage(true);
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return number_format($bytes / (1024 ** $power), 2) . ' ' . ($units[$power] ?? 'B');
    }

    /**
     * Microsecond diagnostics benchmark payload.
     */
    public static function bench(): array
    {
        return [
            'engine'       => 'Srin Engine',
            'version'      => self::VERSION,
            'elapsed'      => self::elapsed(),
            'memory'       => self::memory(),
            'peak_memory'  => self::peakMemory(),
            'php_version'  => PHP_VERSION,
            'sapi'         => PHP_SAPI,
            'services'     => count(self::$bindings),
        ];
    }

    /**
     * Fast Boot & Ignite the application lifecycle.
     */
    public static function start(): void
    {
        if (self::$booted) {
            return;
        }

        self::$startTime = microtime(true);

        // 1. Load environment variables
        App::loadEnv();

        // 2. Send Hardened Security Headers
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header_remove('X-Powered-By');
        }

        // 3. Start Hardened Session
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', '1');
            }
            session_start();
        }

        // 4. Register class alias for BaseController
        if (!class_exists('Controller\BaseController', false)) {
            class_alias(BaseController::class, 'Controller\BaseController');
        }

        // 5. Configure timezone and error reporting
        date_default_timezone_set(config('app.timezone', 'UTC'));
        $isDebug = (bool) config('app.debug', true);

        if ($isDebug) {
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', '0');
            error_reporting(0);
        }

        self::$booted = true;

        // 6. Trigger 'boot' lifecycle event
        self::trigger('boot');

        // 7. Route and Dispatch through dynamic middleware pipeline
        try {
            $uri = Request::uri();
            self::trigger('request', $uri);

            // Load web routes
            $routesFile = dirname(__DIR__) . '/Routes/web.php';
            if (file_exists($routesFile)) {
                require_once $routesFile;
            }

            // Run through registered dynamic middlewares
            self::runPipeline(function () {
                Router::dispatch();
            });

            // Trigger 'response' and 'terminate' lifecycle hooks
            self::trigger('response');
            self::trigger('terminate');
        } catch (Throwable $e) {
            self::trigger('error', $e);
            App::handleException($e, $isDebug);
        }
    }

    /**
     * Execute registered dynamic middlewares in pipeline order.
     */
    protected static function runPipeline(callable $destination): void
    {
        $pipeline = array_reverse(self::$middlewares);

        $next = $destination;
        foreach ($pipeline as $middleware) {
            $currentNext = $next;
            $next = function () use ($middleware, $currentNext) {
                if (is_callable($middleware)) {
                    return $middleware(Request::uri(), $currentNext);
                }
                if (is_string($middleware) && class_exists($middleware)) {
                    $instance = new $middleware();
                    if (method_exists($instance, 'handle')) {
                        return $instance->handle(Request::uri(), $currentNext);
                    }
                }
                return $currentNext();
            };
        }

        $next();
    }

    /**
     * Check if currently running in CLI mode.
     */
    public static function isCli(): bool
    {
        return PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';
    }

    /**
     * Check if currently in development environment.
     */
    public static function isDev(): bool
    {
        return config('app.env', 'development') === 'development';
    }

    /**
     * Check if currently in production environment.
     */
    public static function isProd(): bool
    {
        return config('app.env', 'development') === 'production';
    }
}
