<?php

namespace Core;

/**
 * ============================================================================
 * Srin Engine - Unified Security & Anti-Hacking Guard
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 *
 * Provides enterprise-grade defenses against:
 *   1. Cross-Site Scripting (XSS)
 *   2. Cross-Site Request Forgery (CSRF)
 *   3. Automated Spam & Bot Attacks (Honeypot)
 *   4. Brute-Force & Denial-of-Service (Rate Limiting)
 *   5. HTTP Response Splitting / CRLF Injection
 *   6. Null-Byte Poisoning & Directory Traversal
 *   7. Form Tampering & Mass Assignment
 */
class Security
{
    /**
     * Retrieve or generate the current CSRF session token.
     * Uses cryptographically secure random bytes.
     */
    public static function csrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['_srin_csrf_token'])) {
            $_SESSION['_srin_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_srin_csrf_token'];
    }

    /**
     * Regenerate and rotate the CSRF session token.
     */
    public static function regenerateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['_srin_csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['_srin_csrf_token'];
    }

    /**
     * Generate an HTML hidden input field containing the CSRF token.
     */
    public static function csrfField(): string
    {
        $token = htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }

    /**
     * Verify submitted CSRF token using constant-time string comparison.
     * Checks POST data (_csrf, _token) and HTTP headers (X-CSRF-TOKEN, X-XSRF-TOKEN).
     */
    public static function verifyCsrf(?string $token = null): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $stored = $_SESSION['_srin_csrf_token'] ?? '';
        if (empty($stored)) {
            return false;
        }

        // 1. If explicit token passed
        if ($token !== null && hash_equals($stored, $token)) {
            return true;
        }

        // 2. Check $_POST payload
        $postToken = $_POST['_csrf'] ?? $_POST['_token'] ?? null;
        if (is_string($postToken) && hash_equals($stored, $postToken)) {
            return true;
        }

        // 3. Check HTTP headers (for AJAX / fetch requests)
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_XSRF_TOKEN'] ?? null;
        if (is_string($headerToken) && hash_equals($stored, $headerToken)) {
            return true;
        }

        return false;
    }

    /**
     * Enforce CSRF verification on form submissions.
     * Aborts with HTTP 419 Page Expired if the token is invalid or missing.
     */
    public static function enforceCsrf(): void
    {
        if (!self::verifyCsrf()) {
            Response::renderError(419, 'CSRF verification failed or page expired. Please refresh the page and try again.');
            exit;
        }
    }

    /**
     * Generate a hidden honeypot form field to catch automated bots.
     * Invisible to human visitors via CSS; bots fill all inputs.
     */
    public static function honeypotField(string $fieldName = '_srin_hp_check'): string
    {
        return '<div style="position: absolute; left: -9999px; top: -9999px; opacity: 0; pointer-events: none;" aria-hidden="true">'
            . '<label for="' . $fieldName . '">Leave this field blank</label>'
            . '<input type="text" name="' . $fieldName . '" id="' . $fieldName . '" tabindex="-1" autocomplete="off" value="">'
            . '</div>';
    }

    /**
     * Verify that the honeypot field was left empty by a human visitor.
     * Returns true if safe (empty), false if filled by a bot.
     */
    public static function verifyHoneypot(string $fieldName = '_srin_hp_check'): bool
    {
        $val = $_POST[$fieldName] ?? $_GET[$fieldName] ?? '';
        return trim((string) $val) === '';
    }

    /**
     * Enforce Honeypot anti-bot verification.
     * If a bot submitted the form, silently rejects or aborts with 400 Bad Request.
     */
    public static function enforceHoneypot(string $fieldName = '_srin_hp_check'): void
    {
        if (!self::verifyHoneypot($fieldName)) {
            Response::renderError(400, 'Automated spam submission detected.');
            exit;
        }
    }

    /**
     * Rate limiter to prevent brute-force attacks and form flooding.
     * Tracks attempts per IP + action key within a time window.
     *
     * @param string $key           Unique action key (e.g. 'contact-form', 'login')
     * @param int    $maxAttempts   Maximum allowed attempts within the decay window
     * @param int    $decaySeconds  Window duration in seconds (default: 60 seconds)
     * @return bool                 True if within limit, False if exceeded
     */
    public static function rateLimit(string $key, int $maxAttempts = 5, int $decaySeconds = 60): bool
    {
        try {
            $ip = Request::ip();
            $rateKey = hash('sha256', $key . '|' . $ip);
            $cacheDir = sys_get_temp_dir() . '/srin_rates';
            if (!is_dir($cacheDir)) {
                if (!@mkdir($cacheDir, 0700, true) && !is_dir($cacheDir)) {
                    return true; // Fail open if storage is unavailable
                }
            }

            // Opportunistic cleanup of expired rate limit files (~1% of requests)
            if (random_int(1, 100) === 1) {
                $nowTs = time();
                $files = @glob($cacheDir . '/*.json');
                if (is_array($files)) {
                    foreach ($files as $file) {
                        if (@filemtime($file) < ($nowTs - $decaySeconds)) {
                            @unlink($file);
                        }
                    }
                }
            }

            $cacheFile = $cacheDir . '/' . $rateKey . '.json';
            $now = time();

            $fp = @fopen($cacheFile, 'c+');
            if ($fp === false) {
                return true; // Fail open if storage is unavailable
            }

            if (!flock($fp, LOCK_EX)) {
                fclose($fp);
                return true; // Fail open
            }

            $contents = stream_get_contents($fp);
            $record = ['attempts' => 0, 'reset' => $now + $decaySeconds];
            if (!empty($contents)) {
                $data = json_decode($contents, true);
                if (is_array($data) && isset($data['attempts'], $data['reset'])) {
                    $record = $data;
                }
            }

            // Reset if window expired
            if ($now > $record['reset']) {
                $record = ['attempts' => 1, 'reset' => $now + $decaySeconds];
                $allowed = true;
            } else {
                // Increment attempt
                $record['attempts']++;
                $allowed = $record['attempts'] <= $maxAttempts;
            }

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($record));
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);

            return $allowed;
        } catch (\Throwable $e) {
            return true; // Fail open
        }
    }

    /**
     * Enforce rate limiting.
     * Aborts with HTTP 429 Too Many Requests if the limit is exceeded.
     */
    public static function enforceRateLimit(string $key, int $maxAttempts = 5, int $decaySeconds = 60): void
    {
        if (!self::rateLimit($key, $maxAttempts, $decaySeconds)) {
            Response::renderError(429, 'Too many requests. Please slow down and try again shortly.');
            exit;
        }
    }

    /**
     * Sanitize a string against null-byte poisoning, invisible control chars, and CRLF injection.
     */
    public static function cleanString(mixed $val, bool $stripNewlines = true): string
    {
        if (!is_string($val)) {
            $val = (string) $val;
        }

        // Sanitize invalid UTF-8 sequences to prevent preg_replace returning null & triggering deprecation
        $val = mb_convert_encoding($val, 'UTF-8', 'UTF-8');

        if ($stripNewlines) {
            // Strip null bytes, invisible control chars, and CRLF
            $val = preg_replace('/[\x00-\x1F\x7F]/u', '', $val);
        } else {
            // Strip null bytes and control chars (except normal tab, newline, carriage return)
            $val = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $val);
        }

        return trim((string) $val);
    }

    /**
     * Recursively clean an array of inputs.
     */
    public static function cleanArray(array $arr): array
    {
        $cleaned = [];
        foreach ($arr as $k => $v) {
            $safeKey = self::cleanString((string) $k);
            if (is_array($v)) {
                $cleaned[$safeKey] = self::cleanArray($v);
            } else {
                $cleaned[$safeKey] = self::cleanString($v);
            }
        }
        return $cleaned;
    }

    /**
     * Sanitize a URL to prevent HTTP Response Splitting / Header Injection.
     */
    public static function cleanUrl(string $url): string
    {
        return str_replace(["\r", "\n", "\0"], '', trim($url));
    }

    /**
     * Escape HTML output safely (XSS protection).
     */
    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

