<?php

namespace Core;

use Closure;

/**
 * ============================================================================
 * Srin Engine - Link (Fluent Route Mapper)
 * ============================================================================
 * Developed by Arindam Makar
 * GitHub: https://github.com/deadxfire
 *
 * Standalone route mapper providing clean, expressive link registration:
 *   Link::get('/about', 'AboutController@index')->name('about');
 *   Link::post('/contact', 'ContactController@sendMail')->name('contact.send');
 *   Link::group(['prefix' => '/admin', 'checkpoint' => 'auth'], function() { ... });
 *
 * Also available globally via:
 *   getlink('/about', 'AboutController@index')->name('about');
 *   postlink('/contact', 'ContactController@sendMail')->name('contact.send');
 * ============================================================================
 */
class Link
{
    /**
     * Register a GET route link.
     */
    public static function get(string $path, mixed $handler, ?string $name = null): Route
    {
        return Router::get($path, $handler, $name);
    }

    /**
     * Register a POST route link.
     */
    public static function post(string $path, mixed $handler, ?string $name = null): Route
    {
        return Router::post($path, $handler, $name);
    }

    /**
     * Register a PUT route link.
     */
    public static function put(string $path, mixed $handler, ?string $name = null): Route
    {
        return Router::put($path, $handler, $name);
    }

    /**
     * Register a DELETE route link.
     */
    public static function delete(string $path, mixed $handler, ?string $name = null): Route
    {
        return Router::delete($path, $handler, $name);
    }

    /**
     * Register a PATCH route link.
     */
    public static function patch(string $path, mixed $handler, ?string $name = null): Route
    {
        return Router::patch($path, $handler, $name);
    }

    /**
     * Register an OPTIONS route link.
     */
    public static function options(string $path, mixed $handler, ?string $name = null): Route
    {
        return Router::options($path, $handler, $name);
    }

    /**
     * Register a route link responding to any HTTP method.
     */
    public static function any(string $path, mixed $handler, ?string $name = null): Route
    {
        return Router::any($path, $handler, $name);
    }

    /**
     * Register a route link group sharing attributes (prefix, checkpoint, name).
     */
    public static function group(array $attributes, Closure $callback): void
    {
        Router::group($attributes, $callback);
    }
}

