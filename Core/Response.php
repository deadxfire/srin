<?php

namespace Core;

class Response
{
    /**
     * Send HTTP status code.
     */
    public static function status(int $code): void
    {
        if (!headers_sent()) {
            http_response_code($code);
        }
    }

    /**
     * Send a JSON response and terminate.
     */
    public static function json(mixed $data, int $statusCode = 200): void
    {
        self::status($statusCode);
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        if (Request::method() !== 'HEAD') {
            echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        if (class_exists(Igniter::class)) {
            Igniter::trigger('response');
            Igniter::trigger('terminate');
        }
        exit;
    }

    /**
     * Redirect to another URL and terminate.
     */
    public static function redirect(string $url, int $statusCode = 302): void
    {
        self::status($statusCode);
        $cleanUrl = Security::cleanUrl($url);
        if (!headers_sent()) {
            header("Location: {$cleanUrl}");
        }
        if (class_exists(Igniter::class)) {
            Igniter::trigger('response');
            Igniter::trigger('terminate');
        }
        exit;
    }

    /**
     * Redirect back to previous URL (HTTP_REFERER) or a fallback route.
     * Prevents open-redirect attacks by strictly validating that Referer matches the application host.
     */
    public static function back(int $statusCode = 302, string $fallback = '/'): void
    {
        $referer = trim((string) ($_SERVER['HTTP_REFERER'] ?? ''));
        $target = (str_starts_with($fallback, '/') && !str_starts_with($fallback, '//')) ? $fallback : '/';

        if ($referer !== '' && filter_var($referer, FILTER_VALIDATE_URL)) {
            $refScheme = strtolower((string) parse_url($referer, PHP_URL_SCHEME));
            $refHost = strtolower((string) parse_url($referer, PHP_URL_HOST));

            $currentHost = strtolower((string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost'));
            if (str_contains($currentHost, ':')) {
                $currentHost = explode(':', $currentHost)[0];
            }

            // Only allow redirect to same host via http or https
            if (in_array($refScheme, ['http', 'https'], true) && $refHost !== '' && $refHost === $currentHost) {
                $target = $referer;
            }
        } elseif (str_starts_with($referer, '/') && !str_starts_with($referer, '//')) {
            $target = $referer;
        }

        self::redirect($target, $statusCode);
    }

    /**
     * Output HTML content.
     */
    public static function html(string $content, int $statusCode = 200): void
    {
        self::status($statusCode);
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
        }
        if (Request::method() !== 'HEAD') {
            echo $content;
        }
    }

    /**
     * Securely download a file.
     * Enforces STRICT isolation: Files can ONLY be accessed from the assets/ directory.
     * Direct downloads from Pages/ or any other core directory are strictly prohibited.
     */
    public static function download(string $assetPath, ?string $downloadName = null): void
    {
        // Reject traversal sequences and NUL bytes outright
        $decoded = rawurldecode($assetPath);
        if (
            str_contains($assetPath, "\0") || str_contains($assetPath, '..') ||
            str_contains($decoded, "\0") || str_contains($decoded, '..')
        ) {
            self::renderError(404, "Downloadable file not found.");
            exit;
        }

        $clean = ltrim(str_replace(['\\', '/'], '/', $assetPath), '/');
        if (str_starts_with($clean, 'assets/')) {
            $clean = substr($clean, 7);
        }

        $baseDir = dirname(__DIR__);
        $assetsDir = realpath($baseDir . '/assets');
        $pagesDir = realpath($baseDir . '/Pages');

        // Resolve absolute target file path
        $targetPath = realpath($assetsDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $clean));

        // Security Check 1: File must exist and be a regular file
        if (!$targetPath || !is_file($targetPath)) {
            self::renderError(404, "Downloadable file not found.");
            exit;
        }

        // Security Check 2: Strictly enforce assets/ boundary with trailing separator
        if (!$assetsDir || !str_starts_with($targetPath, $assetsDir . DIRECTORY_SEPARATOR)) {
            self::renderError(404, "Downloadable file not found.");
            exit;
        }

        // Security Check 3: Explicitly block any attempt to download from Pages/ (throws 404)
        if ($pagesDir && str_starts_with($targetPath, $pagesDir . DIRECTORY_SEPARATOR)) {
            self::renderError(404, "Downloadable file not found.");
            exit;
        }

        // Clean output buffer to ensure clean binary download
        if (ob_get_level()) {
            ob_end_clean();
        }

        $filename = ($downloadName !== null && $downloadName !== '') ? $downloadName : basename($targetPath);
        $filename = str_replace(["\r", "\n", "\0", '/', '\\', '"', "'"], '', $filename);
        $filename = trim($filename);
        if ($filename === '') {
            $filename = basename($targetPath);
            $filename = str_replace(["\r", "\n", "\0", '/', '\\', '"', "'"], '', $filename);
        }

        $mimeType = mime_content_type($targetPath) ?: 'application/octet-stream';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($targetPath));
        header('X-Content-Type-Options: nosniff');

        readfile($targetPath);
        exit;
    }

    /**
     * Render an HTTP error page.
     * Automatically resolves custom error templates by error number:
     * 1. Pages/errors/{code}.srin.php
     * 2. Pages/{code}.srin.php
     * 3. Built-in clean fallback design
     */
    public static function renderError(int $code, string $message = ''): void
    {
        self::status($code);

        if (Request::method() === 'HEAD') {
            if (class_exists(Igniter::class)) {
                Igniter::trigger('response');
                Igniter::trigger('terminate');
            }
            exit;
        }

        $statusPhrases = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Page Not Found',
            405 => 'Method Not Allowed',
            408 => 'Request Timeout',
            419 => 'Page Expired',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
        ];

        $statusText = $statusPhrases[$code] ?? 'Error';
        $finalMessage = $message !== '' ? $message : $statusText;
        $title = "{$code} - {$statusText}";

        $pagesDir = dirname(__DIR__) . '/Pages';
        $errorFileSrin = $pagesDir . "/errors/{$code}.srin.php";
        $directFileSrin = $pagesDir . "/{$code}.srin.php";

        $errorData = [
            'code' => $code,
            'statusText' => $statusText,
            'message' => $finalMessage,
            'title' => $title,
        ];

        // 1. Look for Pages/errors/{code}.srin.php
        if (file_exists($errorFileSrin)) {
            $output = View::render("errors/{$code}", $errorData);
        } elseif (file_exists($directFileSrin)) {
            // 2. Look for Pages/{code}.srin.php
            $output = View::render((string) $code, $errorData);
        } else {
            // 3. Fallback built-in design
            $output = self::defaultErrorPage($code, $statusText, $finalMessage);
        }

        if (class_exists(Igniter::class)) {
            Igniter::trigger('response', $output);
        }
        echo $output;
        if (class_exists(Igniter::class)) {
            Igniter::trigger('terminate');
        }
        exit;
    }

    /**
     * Abort request with HTTP status code and message.
     */
    public static function abort(int $code = 404, string $message = ''): void
    {
        self::renderError($code, $message);
    }

    /**
     * Built-in fallback error page.
     */
    protected static function defaultErrorPage(int $code, string $statusText, string $message): string
    {
        $homeUrl = function_exists('url') ? url('/') : '/';
        $safeStatus = htmlspecialchars($statusText, ENT_QUOTES, 'UTF-8');
        $safeMsg = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$code} - {$safeStatus}</title>
            <style>
                *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    background-color: #0b0f19;
                    color: #f8fafc;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 24px;
                }
                .error-card {
                    background: #111827;
                    border: 1px solid #1f2937;
                    border-radius: 16px;
                    max-width: 540px;
                    width: 100%;
                    padding: 44px 32px;
                    text-align: center;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                }
                .error-badge {
                    display: inline-block;
                    font-family: ui-monospace, Menlo, Consolas, monospace;
                    font-size: 4.5rem;
                    font-weight: 800;
                    line-height: 1;
                    color: #ef4444;
                    margin-bottom: 12px;
                    letter-spacing: -2px;
                }
                h1 {
                    font-size: 1.6rem;
                    font-weight: 700;
                    color: #f1f5f9;
                    margin-bottom: 12px;
                }
                p.error-desc {
                    color: #94a3b8;
                    font-size: 1.05rem;
                    line-height: 1.6;
                    margin-bottom: 24px;
                }
                .btn-home {
                    display: inline-block;
                    background: #2563eb;
                    color: #ffffff;
                    text-decoration: none;
                    font-weight: 600;
                    padding: 12px 28px;
                    border-radius: 8px;
                    transition: background 0.2s;
                }
                .btn-home:hover {
                    background: #1d4ed8;
                }
                .hint-box {
                    margin-top: 30px;
                    padding: 14px 18px;
                    background: #1e293b;
                    border: 1px dashed #334155;
                    border-radius: 8px;
                    font-size: 0.85rem;
                    color: #94a3b8;
                    text-align: left;
                    line-height: 1.5;
                }
                .hint-box code {
                    color: #38bdf8;
                    font-family: ui-monospace, Consolas, monospace;
                }
                .developer-credit {
                    margin-top: 24px;
                    font-size: 0.82rem;
                    color: #64748b;
                }
                .developer-credit strong {
                    color: #94a3b8;
                }
            </style>
        </head>
        <body>
            <div class="error-card">
                <div class="error-badge">{$code}</div>
                <h1>{$safeStatus}</h1>
                <p class="error-desc">{$safeMsg}</p>
                <a href="{$homeUrl}" class="btn-home">&larr; Return to Home</a>
                <div class="hint-box">
                    💡 <strong>Custom Design:</strong> Create <code>Pages/errors/{$code}.srin.php</code> (or run <code>php srin make:error {$code}</code>) to automatically customize this error page.
                </div>
                <div class="developer-credit">
                    Srin Engine &bull; Developed by <strong><a href="https://github.com/deadxfire" target="_blank" style="color: inherit; text-decoration: underline;">Arindam Makar</a></strong>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}
