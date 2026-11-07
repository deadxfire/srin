<?php

namespace Core;

use Exception;

/**
 * ============================================================================
 * Srin Engine - Base Database Seeder
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 */
abstract class Seeder
{
    /**
     * Run the database seeder logic.
     */
    abstract public function run(): void;

    /**
     * Run one or more seeder classes.
     *
     * @param array|string $class
     */
    public function call(array|string $class): void
    {
        $classes = is_array($class) ? $class : func_get_args();

        foreach ($classes as $c) {
            $instance = $this->resolveSeeder($c);
            $className = is_object($c) ? get_class($c) : $c;
            $shortName = basename(str_replace('\\', '/', $className));

            echo "  " . (function_exists('color') ? color('[DONE]', 'green') : '[DONE]') . " Seeded: {$shortName}\n";
            $instance->run();
        }
    }

    /**
     * Resolve a seeder class instance, auto-loading from database/seeders/ if needed.
     */
    protected function resolveSeeder(string|object $class): Seeder
    {
        if (is_object($class)) {
            return $class;
        }

        if (class_exists($class)) {
            return new $class();
        }

        // Try to load from database/seeders/ directory
        $cleanName = str_replace(['Database\\Seeders\\', '\\'], ['', '/'], $class);
        $file = dirname(__DIR__) . "/database/seeders/{$cleanName}.php";

        if (file_exists($file)) {
            require_once $file;

            if (class_exists($class)) {
                return new $class();
            }

            $shortName = basename(str_replace('\\', '/', $class));
            if (class_exists($shortName)) {
                return new $shortName();
            }

            $namespaced = "Database\\Seeders\\{$shortName}";
            if (class_exists($namespaced)) {
                return new $namespaced();
            }
        }

        throw new Exception("Seeder class [{$class}] could not be found or loaded from database/seeders/.");
    }
}
