<?php

namespace Core;

use Throwable;

class App
{
    /**
     * Current framework version.
     */
    public const VERSION = '1.2.0';

    /**
     * Load environment variables from .env file into $_ENV, $_SERVER, and putenv().
     */
    public static function loadEnv(?string $path = null): void
    {
        $envPath = $path ?: dirname(__DIR__) . '/.env';
        if (!file_exists($envPath) || !is_readable($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            // Skip comments and empty lines
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Must contain '='
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Strip enclosing quotes: "value" or 'value'
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            // Populate $_ENV, $_SERVER, and putenv only if not already set by system/server environment
            $hasExisting = array_key_exists($key, $_ENV) || array_key_exists($key, $_SERVER) || getenv($key) !== false;
            if (!$hasExisting) {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    /**
     * Run the application via the fast dynamic Igniter lifecycle engine.
     */
    public static function run(): void
    {
        Igniter::start();
    }

    /**
     * Handle uncaught exceptions cleanly.
     */
    public static function handleException(Throwable $e, bool $isDebug): void
    {
        // 1. Automatically log error event to Others/logs/srin.log
        Log::error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        Response::status(500);

        if ($isDebug) {
            $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            $file = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
            $line = $e->getLine();
            $trace = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');

            echo <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Application Error</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 40px 20px; line-height: 1.6; }
                    .error-container { max-width: 900px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 30px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); border-left: 6px solid #ef4444; }
                    h1 { color: #f87171; margin-top: 0; font-size: 1.8rem; }
                    .meta { color: #94a3b8; font-size: 0.95rem; margin-bottom: 20px; }
                    pre { background: #0f172a; padding: 15px; border-radius: 8px; overflow-x: auto; color: #cbd5e1; font-size: 0.85rem; }
                    .author-foot { margin-top: 25px; padding-top: 15px; border-top: 1px solid #334155; font-size: 0.85rem; color: #94a3b8; }
                    .author-foot a { color: #38bdf8; text-decoration: none; }
                </style>
            </head>
            <body>
                <div class="error-container">
                    <h1>Application Error</h1>
                    <p><strong>Message:</strong> {$msg}</p>
                    <div class="meta"><strong>File:</strong> {$file} on line <strong>{$line}</strong></div>
                    <h3>Stack Trace</h3>
                    <pre>{$trace}</pre>
                    <div class="author-foot">Srin Engine &bull; Developed by <strong><a href="https://github.com/deadxfire" target="_blank">Arindam Makar</a></strong></div>
                </div>
            </body>
            </html>
            HTML;
        } else {
            // Production: automatically render Pages/errors/500.srin.php if present
            Response::renderError(500, 'Something went wrong on our end. Please try again later.');
        }
    }
}
