<?php

namespace Core;

/**
 * ============================================================================
 * Srin Engine - Logging Subsystem
 * ============================================================================
 * Developed by Arindam Makar
 * 
 * Simple, high-speed, file-based logger.
 * Stores logs by default in Others/logs/srin.log.
 * Can be inspected in terminal via `php srin logs`.
 * ============================================================================
 */
class Log
{
    /**
     * Custom log directory path (defaults to Others/logs).
     */
    protected static string $logDir = '';

    /**
     * Resolve the log directory path and ensure it exists.
     */
    public static function getLogDir(): string
    {
        if (self::$logDir === '') {
            self::$logDir = dirname(__DIR__) . '/Others/logs';
        }
        if (!is_dir(self::$logDir)) {
            @mkdir(self::$logDir, 0750, true);
        }
        $ht = self::$logDir . '/.htaccess';
        if (!file_exists($ht)) {
            @file_put_contents($ht, "Order Deny,Allow\nDeny from all\n");
        }
        return self::$logDir;
    }

    /**
     * Get path to the main application log file.
     */
    public static function getLogFile(): string
    {
        return self::getLogDir() . '/srin.log';
    }

    /**
     * Write an entry to the log file.
     */
    public static function write(string $level, string $message, array $context = []): void
    {
        $file = self::getLogFile();
        $timestamp = date('Y-m-d H:i:s');
        $levelUpper = strtoupper(trim($level));

        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        $entry = "[{$timestamp}] {$levelUpper}: {$message}{$contextStr}" . PHP_EOL;

        @file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log an informational message.
     */
    public static function info(string $message, array $context = []): void
    {
        self::write('info', $message, $context);
    }

    /**
     * Log an error event.
     */
    public static function error(string $message, array $context = []): void
    {
        self::write('error', $message, $context);
    }

    /**
     * Log a warning event.
     */
    public static function warning(string $message, array $context = []): void
    {
        self::write('warning', $message, $context);
    }

    /**
     * Log a debug trace message.
     */
    public static function debug(string $message, array $context = []): void
    {
        self::write('debug', $message, $context);
    }

    /**
     * Clear the main application log file.
     */
    public static function clear(): bool
    {
        $file = self::getLogFile();
        if (file_exists($file)) {
            return @file_put_contents($file, '') !== false;
        }
        return true;
    }
}

