<?php

namespace Core;

use Exception;

/**
 * ============================================================================
 * Srin Engine - Checkpoint System (Linear Request Verification Gate)
 * ============================================================================
 * An intuitive, linear alternative to traditional onion middleware.
 *
 * Instead of nested closure hell ($next($request)), a Checkpoint implements:
 *   - enter(): Return true or null to ALLOW passage.
 *              Return a redirect, Response, or false to HALT and respond.
 *   - leave(): Optional post-controller filter modifying output.
 * ============================================================================
 */
abstract class Checkpoint
{
    /**
     * Registered checkpoint aliases.
     * @var array<string, string>
     */
    protected static array $registry = [
        'auth'  => \Checkpoint\AuthCheckpoint::class,
        'guest' => \Checkpoint\GuestCheckpoint::class,
        'admin' => \Checkpoint\AdminCheckpoint::class,
    ];

    /**
     * Register a custom checkpoint alias.
     * Example: Checkpoint::register('verified', VerifiedEmailCheckpoint::class);
     */
    public static function register(string $alias, string $className): void
    {
        self::$registry[strtolower(trim($alias))] = $className;
    }

    /**
     * Get all registered checkpoint aliases.
     */
    public static function getRegistry(): array
    {
        return self::$registry;
    }

    /**
     * Inspect the incoming request before the controller action executes.
     *
     * @return mixed Return true or null to ALLOW passage.
     *               Return a redirect, Response, or false to HALT immediately.
     */
    abstract public function enter(): mixed;

    /**
     * Optional filter executed after the controller action finishes.
     * Receives the controller's returned output, and can modify or enrich it.
     */
    public function leave(mixed $result): mixed
    {
        return $result;
    }

    /**
     * Resolve a checkpoint instance by alias or class name.
     */
    public static function resolve(string|object $checkpoint): Checkpoint
    {
        if (is_object($checkpoint)) {
            if ($checkpoint instanceof Checkpoint) {
                return $checkpoint;
            }
            throw new Exception("Object of class [" . get_class($checkpoint) . "] must extend Core\\Checkpoint.");
        }

        $alias = strtolower(trim($checkpoint));
        $className = self::$registry[$alias] ?? $checkpoint;

        if (!class_exists($className)) {
            // Check in Checkpoint\ namespace
            $namespaced = "Checkpoint\\" . ucfirst($checkpoint) . "Checkpoint";
            if (class_exists($namespaced)) {
                $className = $namespaced;
            } else {
                throw new Exception("Checkpoint [{$checkpoint}] could not be resolved. Class [{$className}] not found.");
            }
        }

        $instance = new $className();
        if (!($instance instanceof Checkpoint)) {
            throw new Exception("Class [{$className}] must extend Core\\Checkpoint.");
        }

        return $instance;
    }

    /**
     * Run a sequence of checkpoints around an action.
     * Halts immediately if any checkpoint returns a non-true / blocking response.
     */
    public static function run(array $checkpoints, callable $action): mixed
    {
        $instances = [];

        // 1. Execute enter() phase
        foreach ($checkpoints as $cp) {
            $instance = self::resolve($cp);
            $instances[] = $instance;

            $status = $instance->enter();

            // If checkpoint returned false, an abort/redirect was already sent or blocked
            if ($status === false) {
                return null;
            }

            // If checkpoint returned a redirect string, Response object, or HTML string
            if ($status !== true && $status !== null) {
                return $status;
            }
        }

        // 2. Execute target action (controller method or closure)
        $result = $action();

        // 3. Execute leave() phase in reverse order (LIFO)
        for ($i = count($instances) - 1; $i >= 0; $i--) {
            $result = $instances[$i]->leave($result);
        }

        return $result;
    }
}

