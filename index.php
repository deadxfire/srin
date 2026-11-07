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
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $targetFile = realpath(__DIR__ . $uriPath);

    if ($targetFile && is_file($targetFile)) {
        $realAssets = realpath(__DIR__ . '/assets');

        // Only allow direct static streaming if the file resides STRICTLY inside the assets/ directory
        if ($realAssets && str_starts_with($targetFile, $realAssets)) {
            return false; // Valid asset file -> let PHP CLI server stream it directly
        }

        // For non-asset files (e.g. /core/App.php, /Config/mail.php, /Pages/...), do NOT return false.
        // Let execution fall through to \Core\App::run(), where Router dispatches it as an unmapped route and renders 404!
    }
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
