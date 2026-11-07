<?php

namespace Core;

class Request
{
    /**
     * Get the current request URI without query string.
     * Automatically strips subfolder paths if installed in a subdirectory.
     */
    public static function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($uri, '?');
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }

        // Auto-detect and strip subdirectory if deployed in a subfolder
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($baseDir === '.' || $baseDir === '/') {
            $baseDir = '';
        }

        if ($baseDir !== '' && str_starts_with($uri, $baseDir)) {
            $uri = substr($uri, strlen($baseDir));
        }

        // Normalize trailing slash (except root)
        $uri = rtrim($uri, '/');
        return $uri === '' ? '/' : $uri;
    }

    /**
     * Get the HTTP request method.
     */
    public static function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper((string) $_POST['_method']);
        }

        return $method;
    }

    /**
     * Get all inputs from GET and POST.
     */
    public static function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    /**
     * Get a specific input value.
     */
    public static function input(string $key, mixed $default = null): mixed
    {
        $data = self::all();
        return $data[$key] ?? $default;
    }

    /**
     * Check if a specific input is present.
     */
    public static function has(string $key): bool
    {
        $data = self::all();
        return isset($data[$key]);
    }

    /**
     * Check if request is POST.
     */
    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    /**
     * Check if request is GET.
     */
    public static function isGet(): bool
    {
        return self::method() === 'GET';
    }

    /**
     * Check if request is AJAX.
     */
    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get the client IP address.
     */
    public static function ip(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ??
               $_SERVER['REMOTE_ADDR'] ??
               '127.0.0.1';
    }

    /**
     * Get the user agent string.
     */
    public static function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
}

