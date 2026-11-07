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
     * Supports method spoofing via _method input field (PUT, PATCH, DELETE).
     */
    public static function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if ($method === 'POST' && isset($_POST['_method'])) {
            $spoofed = strtoupper(trim((string) $_POST['_method']));
            if (in_array($spoofed, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $spoofed;
            }
        }

        return $method;
    }

    /**
     * Get all inputs from GET and POST sanitized against null-byte poisoning and control characters.
     */
    public static function all(): array
    {
        $raw = array_merge($_GET, $_POST);
        return Security::cleanArray($raw);
    }

    /**
     * Get a specific input value sanitized.
     */
    public static function input(string $key, mixed $default = null): mixed
    {
        $data = self::all();
        return $data[$key] ?? $default;
    }

    /**
     * Get input strictly as a sanitized string.
     * Prevents array injection / type confusion attacks.
     */
    public static function string(string $key, string $default = ''): string
    {
        $val = self::input($key, $default);
        if (is_array($val)) {
            return $default;
        }
        return Security::cleanString((string) $val);
    }

    /**
     * Get input strictly as an integer.
     */
    public static function int(string $key, int $default = 0): int
    {
        $val = self::input($key, $default);
        if (!is_numeric($val)) {
            return $default;
        }
        return (int) $val;
    }

    /**
     * Get input strictly as a boolean.
     */
    public static function bool(string $key, bool $default = false): bool
    {
        $val = self::input($key, null);
        if ($val === null) {
            return $default;
        }
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Whitelist specific input keys (prevents Mass Assignment vulnerabilities).
     */
    public static function only(array $keys): array
    {
        $all = self::all();
        $result = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $all)) {
                $result[$key] = $all[$key];
            }
        }
        return $result;
    }

    /**
     * Get all inputs except specified keys.
     */
    public static function except(array $keys): array
    {
        $all = self::all();
        foreach ($keys as $key) {
            unset($all[$key]);
        }
        return $all;
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
     * Get the client IP address (sanitized).
     */
    public static function ip(): string
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ??
              $_SERVER['REMOTE_ADDR'] ??
              '127.0.0.1';

        // In case multiple comma-separated IPs in proxy header, take the first client IP
        if (str_contains($ip, ',')) {
            $parts = explode(',', $ip);
            $ip = trim($parts[0]);
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
    }

    /**
     * Get the user agent string (sanitized).
     */
    public static function userAgent(): string
    {
        return Security::cleanString($_SERVER['HTTP_USER_AGENT'] ?? '');
    }

    /**
     * Enforce CSRF verification on this request.
     */
    public static function validateCsrf(): void
    {
        Security::enforceCsrf();
    }

    /**
     * Enforce Honeypot verification on this request.
     */
    public static function validateHoneypot(string $field = '_srin_hp_check'): void
    {
        Security::enforceHoneypot($field);
    }
}
