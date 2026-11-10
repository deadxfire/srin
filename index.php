<?php

/**
 * ============================================================================
 * Srin Engine - Core PHP Static Website System
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 * Single entry point for all incoming HTTP requests.
 */

define('SRIN_SECURE', true);
define('SRIN_START', microtime(true));

// Protect against direct file execution / probing when using PHP's built-in server (php srin serve)
if (php_sapi_name() === 'cli-server') {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $decodedUri = rawurldecode($uriPath);

    // Reject traversal sequences and NUL bytes outright
    if (!str_contains($uriPath, "\0") && !str_contains($uriPath, '..') &&
        !str_contains($decodedUri, "\0") && !str_contains($decodedUri, '..')) {
        $realAssets = realpath(__DIR__ . '/assets');
        $targetFile = realpath(__DIR__ . $uriPath);

        if ($realAssets && $targetFile && is_file($targetFile)) {
            // Only allow direct static streaming if the file resides STRICTLY inside the assets/ directory
            if (str_starts_with($targetFile, $realAssets . DIRECTORY_SEPARATOR)) {
                $relative = substr($targetFile, strlen($realAssets . DIRECTORY_SEPARATOR));
                $segments = explode(DIRECTORY_SEPARATOR, $relative);
                $hasDotSegment = false;
                foreach ($segments as $seg) {
                    if (str_starts_with($seg, '.')) {
                        $hasDotSegment = true;
                        break;
                    }
                }

                $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                $blockedExts = ['php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8', 'phps', 'pht', 'htaccess'];

                // Refuse dotfiles and PHP/executable files
                if (!$hasDotSegment && !in_array($ext, $blockedExts, true)) {
                    return false; // Valid asset file -> let PHP CLI server stream it directly
                }
            }
        }
    }

    // For non-asset files (e.g. /assets-private/secret.txt, /Core/App.php, /Config/mail.php, /Pages/...), do NOT return false.
    // Let execution fall through to \Core\App::run(), where Router dispatches it as an unmapped route and renders 404!
}

// 1. Composer Autoloader (packages located in Others/)
$composerAutoload = __DIR__ . '/Others/autoload.php';

if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    // Fallback PSR-4 autoloader for Controller and Core if composer hasn't been run
    spl_autoload_register(function ($class) {
        $prefixes = [
            'Controller\\' => __DIR__ . '/Controller/',
            'Core\\'       => __DIR__ . '/Core/',
            'Config\\'     => __DIR__ . '/Config/',
            'Model\\'      => __DIR__ . '/Model/',
            'Checkpoint\\' => __DIR__ . '/Checkpoint/',
        ];

        foreach ($prefixes as $prefix => $baseDir) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) === 0) {
                $relativeClass = substr($class, $len);
                $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        }
    });

    if (file_exists(__DIR__ . '/Core/Helper.php')) {
        require_once __DIR__ . '/Core/Helper.php';
    }
}

// 2. Fast Dynamic Startup via Srin Igniter
\Core\Igniter::start();
