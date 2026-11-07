<?php

use Core\View;
use Core\Response;
use Core\Security;

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

if (!function_exists('app_origin')) {
    /**
     * Get the base origin (scheme + host + optional subfolder) of the application.
     * Automatically handles reverse proxies, HTTPS, and subdirectories.
     */
    function app_origin(): string
    {
        $configured = '';
        if (function_exists('config')) {
            $configured = (string) (config('base_url') ?: config('app.base_url', ''));
        }
        if ($configured === '' && function_exists('env')) {
            $configured = (string) env('APP_BASE_URL', '');
        }

        // Subdirectory detection from SCRIPT_NAME (e.g. /my-subfolder)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($baseDir === '.' || $baseDir === '/') {
            $baseDir = '';
        }

        // If a full absolute URL is specified in APP_BASE_URL, use it
        if (preg_match('#^https?://#i', $configured)) {
            $url = rtrim($configured, '/');
            if ($baseDir !== '' && !str_ends_with($url, $baseDir)) {
                $url .= $baseDir;
            }
            return $url;
        }

        // Dynamic scheme detection (including reverse proxies)
        $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
            || (!empty($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

        $scheme = $isHttps ? 'https' : 'http';

        // Host detection
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost:8000';
        if (str_contains($host, ',')) {
            $parts = explode(',', $host);
            $host = trim($parts[0]);
        }

        return "{$scheme}://{$host}" . $baseDir;
    }
}

if (!function_exists('asset')) {
    /**
     * Generate a direct, absolute URL for an asset.
     * Automatically maps image filenames directly to assets/images/ without requiring the folder location path.
     * Subfolder and HTTPS safe on all production web servers.
     *
     * Examples:
     *   asset('logo.png')                      => http://localhost:8000/assets/images/logo.png
     *   asset('banner.jpg')                    => http://localhost:8000/assets/images/banner.jpg
     *   asset('images/logo.png')               => http://localhost:8000/assets/images/logo.png
     *   asset('/assets/images/logo.png')       => http://localhost:8000/assets/images/logo.png
     *   asset('css/style.css')                 => http://localhost:8000/assets/css/style.css
     *   asset('')                              => http://localhost:8000/assets/
     *   asset('https://cdn.com/pic.jpg')       => https://cdn.com/pic.jpg
     */
    function asset(string $path = ''): string
    {
        $trimmed = trim($path);

        // 1. If empty, return base assets URL
        if ($trimmed === '') {
            return app_origin() . '/assets/';
        }

        // 2. Return external or protocol-relative URLs as-is
        if (preg_match('#^(https?:)?//#i', $trimmed)) {
            return $trimmed;
        }

        // 3. Normalize slashes
        $cleanPath = str_replace('\\', '/', $trimmed);
        $cleanPath = ltrim($cleanPath, '/');

        // 4. Strip redundant leading 'assets/' prefix if passed
        if (preg_match('#^assets/(.*)$#i', $cleanPath, $matches)) {
            $cleanPath = $matches[1];
        }

        // 5. Separate query string (?v=1.0) and fragment (#anchor) for extension/file checks
        $queryString = '';
        $fragment = '';
        if (($hashPos = strpos($cleanPath, '#')) !== false) {
            $fragment = substr($cleanPath, $hashPos);
            $cleanPath = substr($cleanPath, 0, $hashPos);
        }
        if (($queryPos = strpos($cleanPath, '?')) !== false) {
            $queryString = substr($cleanPath, $queryPos);
            $cleanPath = substr($cleanPath, 0, $queryPos);
        }

        // 6. Automatic image folder resolution:
        // If the path doesn't already start with an asset category folder (images/, css/, js/, downloads/, fonts/),
        // check if it is an image or exists in assets/images/.
        $imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'avif', 'ico', 'bmp', 'tiff'];
        $ext = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));

        $categoryPrefixes = ['images/', 'css/', 'js/', 'downloads/', 'fonts/'];
        $hasCategoryPrefix = false;
        foreach ($categoryPrefixes as $prefix) {
            if (str_starts_with(strtolower($cleanPath), $prefix)) {
                $hasCategoryPrefix = true;
                break;
            }
        }

        if (!$hasCategoryPrefix) {
            $baseDirLocal = dirname(__DIR__);
            $assetsRoot = $baseDirLocal . DIRECTORY_SEPARATOR . 'assets';
            $normalizedRelative = str_replace('/', DIRECTORY_SEPARATOR, $cleanPath);

            $directAssetFile = $assetsRoot . DIRECTORY_SEPARATOR . $normalizedRelative;
            $imageAssetFile  = $assetsRoot . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $normalizedRelative;

            // If file does not exist directly in assets/ root, but exists in assets/images/ OR has an image extension:
            if (!file_exists($directAssetFile) && (file_exists($imageAssetFile) || in_array($ext, $imageExtensions, true))) {
                $cleanPath = 'images/' . $cleanPath;
            }
        }

        // Re-attach query string and fragment
        $cleanPath .= $queryString . $fragment;

        return app_origin() . '/assets/' . $cleanPath;
    }
}

if (!function_exists('assets')) {
    /**
     * Alias for asset() to match {{assets('/css/style.css')}} syntax.
     */
    function assets(string $path = ''): string
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
        return Security::csrfToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Return hidden input field with CSRF token.
     */
    function csrf_field(): string
    {
        return Security::csrfField();
    }
}

if (!function_exists('csrf_verify')) {
    /**
     * Verify submitted CSRF token.
     */
    function csrf_verify(?string $token = null): bool
    {
        return Security::verifyCsrf($token);
    }
}

if (!function_exists('honeypot_field')) {
    /**
     * Generate invisible anti-bot honeypot field.
     */
    function honeypot_field(string $name = '_srin_hp_check'): string
    {
        return Security::honeypotField($name);
    }
}

if (!function_exists('honeypot_verify')) {
    /**
     * Verify honeypot was left blank (human check).
     */
    function honeypot_verify(string $name = '_srin_hp_check'): bool
    {
        return Security::verifyHoneypot($name);
    }
}

if (!function_exists('rate_limit')) {
    /**
     * Check rate limit for an action.
     */
    function rate_limit(string $key, int $max = 5, int $decaySeconds = 60): bool
    {
        return Security::rateLimit($key, $max, $decaySeconds);
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

if (!function_exists('is_home')) {
    /**
     * Check if the current page is the homepage ('/' or route named 'home' / 'index').
     *
     * Example:
     *   @unless(is_home())
     *       <div class="subpage-banner">...</div>
     *   @endunless
     */
    function is_home(): bool
    {
        $uri = \Core\Request::uri();
        if ($uri === '/' || $uri === '' || $uri === '/index.php') {
            return true;
        }

        $routeName = \Core\Router::currentRouteName();
        return $routeName === 'home' || $routeName === 'index';
    }
}

if (!function_exists('is_route')) {
    /**
     * Check if the current route matches a given name or URI pattern.
     * Supports wildcards e.g. is_route('blog.*') or is_route('contact').
     *
     * Example:
     *   @if(!is_route('home'))
     *       <div class="breadcrumb">...</div>
     *   @endif
     */
    function is_route(string|array $patterns): bool
    {
        return \Core\Router::is($patterns);
    }
}

if (!function_exists('request_uri')) {
    /**
     * Get the current request URI without query string.
     */
    function request_uri(): string
    {
        return \Core\Request::uri();
    }
}

if (!function_exists('request_path')) {
    /**
     * Get the current request path (alias of request_uri).
     */
    function request_path(): string
    {
        return \Core\Request::uri();
    }
}




