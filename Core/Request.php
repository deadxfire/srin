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

        if ($baseDir !== '') {
            if ($uri === $baseDir) {
                $uri = '/';
            } elseif (str_starts_with($uri, $baseDir . '/')) {
                $uri = substr($uri, strlen($baseDir));
            }
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
     * Get an uploaded file from $_FILES.
     */
    public static function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Get all uploaded files from $_FILES.
     */
    public static function files(): array
    {
        return $_FILES ?? [];
    }

    /**
     * Check if request has a valid uploaded file.
     */
    public static function hasFile(string $key): bool
    {
        return isset($_FILES[$key]) && is_array($_FILES[$key]) && ($_FILES[$key]['error'] === UPLOAD_ERR_OK) && !empty($_FILES[$key]['tmp_name']);
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
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $trusted = function_exists('config') ? config('app.trusted_proxies', []) : [];
        if (!is_array($trusted) && is_string($trusted) && $trusted !== '') {
            $trusted = array_map('trim', explode(',', $trusted));
        }

        // Only trust X-Forwarded-For if request came from a trusted reverse proxy
        if (!empty($trusted) && in_array($remoteAddr, (array) $trusted, true)) {
            $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
            if ($forwarded) {
                if (str_contains($forwarded, ',')) {
                    $parts = explode(',', $forwarded);
                    $forwarded = trim($parts[0]);
                }
                if (filter_var($forwarded, FILTER_VALIDATE_IP)) {
                    return $forwarded;
                }
            }
        }

        return filter_var($remoteAddr, FILTER_VALIDATE_IP) ? $remoteAddr : '127.0.0.1';
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
