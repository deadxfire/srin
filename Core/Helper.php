<?php

use Core\View;
use Core\Response;

if (!function_exists('env')) {
    /**
     * Get the value of an environment variable with default fallback.
     * Supports boolean, null, and empty string conversions.
     *
     * Example:
     *   env('APP_DEBUG', true)
     *   env('MAIL_HOST', 'smtp.gmail.com')
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        if (is_string($value)) {
            $lower = strtolower($value);
            return match ($lower) {
                'true', '(true)'   => true,
                'false', '(false)' => false,
                'empty', '(empty)' => '',
                'null', '(null)'   => null,
                default            => $value,
            };
        }

        return $value;
    }
}

if (!function_exists('config')) {
    /**
     * Get a configuration value using dot notation or direct key.
     * Config/app.php is the primary/main configuration file.
     *
     * Examples:
     *   config('name')         => looks in Config/app.php ['name']
     *   config('app.name')     => looks in Config/app.php ['name']
     *   config('mail.driver')  => looks in Config/mail.php ['driver']
     *   config()               => returns entire array from Config/app.php
     *   config('app')          => returns entire array from Config/app.php
     */
    function config(?string $key = null, mixed $default = null): mixed
    {
        static $configs = [];

        $loadConfig = function (string $file) use (&$configs): array {
            if (!isset($configs[$file])) {
                $path = dirname(__DIR__) . "/Config/{$file}.php";
                if (file_exists($path)) {
                    $data = require $path;
                    $configs[$file] = is_array($data) ? $data : [];
                } else {
                    $configs[$file] = [];
                }
            }
            return $configs[$file];
        };

        // If no key is passed, return all main app configuration
        if ($key === null) {
            return $loadConfig('app');
        }

        // If requesting a complete configuration array (e.g. config('app') or config('mail'))
        $directFile = dirname(__DIR__) . "/Config/{$key}.php";
        if (file_exists($directFile)) {
            return $loadConfig($key);
        }

        // If key contains dot notation (e.g. 'app.name', 'mail.smtp.host')
        if (str_contains($key, '.')) {
            $parts = explode('.', $key);
            $file = array_shift($parts);
            $current = $loadConfig($file);

            foreach ($parts as $part) {
                if (!is_array($current) || !array_key_exists($part, $current)) {
                    return $default;
                }
                $current = $current[$part];
            }

            return $current;
        }

        // If no dot is present in key, Config/app.php is the MAIN configuration!
        $appConfig = $loadConfig('app');
        if (array_key_exists($key, $appConfig)) {
            return $appConfig[$key];
        }

        return $default;
    }
}

if (!function_exists('view')) {
    /**
     * Render a view from the Pages directory.
     */
    function view(string $page, array $data = []): string
    {
        return View::render($page, $data);
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to another URL or path.
     */
    function redirect(string $url, int $statusCode = 302): void
    {
        Response::redirect($url, $statusCode);
    }
}

if (!function_exists('json')) {
    /**
     * Send a JSON response.
     */
    function json(mixed $data, int $statusCode = 200): void
    {
        Response::json($data, $statusCode);
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset URL.
     * Automatically handles subfolders on any production server.
     */
    function asset(string $path): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($baseDir === '.' || $baseDir === '/') {
            $baseDir = '';
        }
        $cleanPath = ltrim($path, '/');
        
        return ($baseDir !== '' ? $baseDir : '') . '/assets/' . $cleanPath;
    }
}

if (!function_exists('assets')) {
    /**
     * Alias for asset() to match {{assets('/css/style.css')}} syntax.
     */
    function assets(string $path): string
    {
        return asset($path);
    }
}

if (!function_exists('url')) {
    /**
     * Generate an application URL.
     * Automatically handles subfolders on any production server.
     */
    function url(string $path = ''): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($baseDir === '.' || $baseDir === '/') {
            $baseDir = '';
        }
        $cleanPath = ltrim($path, '/');

        $prefix = ($baseDir !== '' ? $baseDir : '');
        return ($cleanPath === '' ? ($prefix ?: '/') : $prefix . '/' . $cleanPath);
    }
}

if (!function_exists('route')) {
    /**
     * Generate a URL for a named route.
     * Example: route('home') or route('post.show', ['id' => 5])
     */
    function route(string $name, array|string|int $params = []): string
    {
        return \Core\Router::getUrlByName($name, $params);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Retrieve or generate CSRF token.
     */
    function csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Return hidden input field with CSRF token.
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('csrf_verify')) {
    /**
     * Verify submitted CSRF token.
     */
    function csrf_verify(?string $token = null): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = $token ?? ($_POST['_csrf'] ?? '');
        $stored = $_SESSION['_csrf_token'] ?? '';

        return !empty($stored) && hash_equals($stored, $token);
    }
}

if (!function_exists('flash')) {
    /**
     * Set or get a flash message in session.
     */
    function flash(string $key, ?string $message = null): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }

        if (isset($_SESSION['_flash'][$key])) {
            $msg = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $msg;
        }

        return null;
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value flashed from previous request.
     */
    function old(string $key, string $default = ''): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('flash_inputs')) {
    /**
     * Flash current request inputs for the next request.
     */
    function flash_inputs(array $inputs): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Do not preserve sensitive data like passwords
        unset($inputs['_csrf'], $inputs['password']);
        $_SESSION['_old'] = $inputs;
    }
}

if (!function_exists('clear_old_inputs')) {
    /**
     * Clear flashed old inputs.
     */
    function clear_old_inputs(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['_old']);
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML output safely.
     */
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('abort')) {
    /**
     * Abort request with an HTTP status code and optional message.
     * Automatically renders Pages/errors/{code}.srin.php if it exists.
     */
    function abort(int $code = 404, string $message = ''): void
    {
        Response::renderError($code, $message);
    }
}

if (!function_exists('download')) {
    /**
     * Download a file securely from the assets/ folder.
     * Enforces strict isolation: non-asset folders (like Pages/) are blocked.
     */
    function download(string $assetPath, ?string $downloadName = null): void
    {
        Response::download($assetPath, $downloadName);
    }
}



