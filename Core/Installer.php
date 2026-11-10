<?php

namespace Core;

/**
 * ============================================================================
 * Srin Engine - Project Installer & Setup Automation
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 *
 * Automatically executed on `composer create-project` or `srin init`.
 * Sets up .env with the project name and generates a cryptographic APP_KEY.
 * ============================================================================
 */
class Installer
{
    /**
     * Post-create-project command handler called automatically by Composer.
     */
    public static function postCreateProject(?object $event = null): void
    {
        $baseDir = dirname(__DIR__);
        self::setup($baseDir);
    }

    /**
     * Configure environment file with project name and encryption key.
     *
     * @param string $baseDir Project root directory
     * @param bool   $force   Whether to force regenerate key if already present
     * @return array Configured attributes ['name' => ..., 'key' => ...]
     */
    public static function setup(string $baseDir, bool $force = false): array
    {
        $envFile = $baseDir . '/.env';
        $exampleFile = $baseDir . '/.env.example';

        // 1. Ensure .env exists (copy from .env.example if missing)
        if (!file_exists($envFile)) {
            if (file_exists($exampleFile)) {
                copy($exampleFile, $envFile);
            } else {
                file_put_contents($envFile, "APP_NAME=\nAPP_KEY=\n");
            }
        }

        $content = file_get_contents($envFile);

        // 2. Determine project name from directory name
        $dirName = basename(realpath($baseDir) ?: $baseDir);
        // Convert hyphens and underscores to spaces, then Title Case
        $projectName = ucwords(trim(str_replace(['-', '_'], ' ', $dirName)));
        if ($projectName === '' || strcasecmp($projectName, 'srin') === 0) {
            $projectName = 'Srin Application';
        }

        // 3. Update APP_NAME and MAIL_FROM_NAME in .env
        if (preg_match('/^APP_NAME=.*$/m', $content)) {
            $content = preg_replace('/^APP_NAME=.*$/m', "APP_NAME=\"{$projectName}\"", $content);
        } else {
            $content = "APP_NAME=\"{$projectName}\"\n" . $content;
        }

        if (preg_match('/^MAIL_FROM_NAME=.*$/m', $content)) {
            $content = preg_replace('/^MAIL_FROM_NAME=.*$/m', "MAIL_FROM_NAME=\"{$projectName}\"", $content);
        }

        // 4. Generate cryptographically secure 256-bit APP_KEY if empty or forced
        $hasKey = preg_match('/^APP_KEY=(.+)$/m', $content, $matches) && trim($matches[1]) !== '';
        $generatedKey = null;

        if (!$hasKey || $force) {
            $generatedKey = 'base64:' . base64_encode(random_bytes(32));
            if (preg_match('/^APP_KEY=.*$/m', $content)) {
                $content = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$generatedKey}", $content);
            } else {
                $content .= "\nAPP_KEY={$generatedKey}\n";
            }
        }

        file_put_contents($envFile, $content);
        @chmod($envFile, 0600);

        // Print confirmation banner
        echo PHP_EOL;
        echo "\033[36m========================================================\033[0m" . PHP_EOL;
        echo "\033[1m              Srin Engine Installed Successfully!       \033[0m" . PHP_EOL;
        echo "\033[33m            Developed by Arindam Makar                  \033[0m" . PHP_EOL;
        echo "\033[36m========================================================\033[0m" . PHP_EOL;
        echo "  [✓] Project Name Configured: \033[32m{$projectName}\033[0m" . PHP_EOL;
        if ($generatedKey !== null) {
            echo "  [✓] Application Key (APP_KEY): \033[32mGenerated (256-bit AES-GCM)\033[0m" . PHP_EOL;
        } else {
            echo "  [✓] Application Key (APP_KEY): \033[32mExisting key preserved\033[0m" . PHP_EOL;
        }
        echo "  [✓] Environment File: \033[32m.env configured\033[0m" . PHP_EOL;
        echo PHP_EOL;
        echo "To start your local development server, run:" . PHP_EOL;
        echo "  \033[1m\033[32msrin dev\033[0m  (or php srin dev)" . PHP_EOL . PHP_EOL;

        return [
            'name' => $projectName,
            'key'  => $generatedKey,
        ];
    }
}
