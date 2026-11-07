<?php

namespace Core;

use Closure;
use Exception;

class Router
{
    /**
     * Stored routes.
     * @var Route[]
     */
    protected static array $routes = [];

    /**
     * Named routes mapping: name => Route.
     * @var array<string, Route>
     */
    protected static array $namedRoutes = [];

    /**
     * Currently matched and active route.
     */
    protected static ?Route $currentRoute = null;

    /**
     * Get the currently active Route.
     */
    public static function current(): ?Route
    {
        return self::$currentRoute;
    }

    /**
     * Get the name of the currently active route.
     */
    public static function currentRouteName(): ?string
    {
        return self::$currentRoute?->getName();
    }

    /**
     * Check if the current route matches a specific name or pattern.
     * Supports exact names ('home') and wildcards ('blog.*').
     */
    public static function is(string|array $patterns): bool
    {
        $currentName = self::currentRouteName();
        $uri = Request::uri();

        $patterns = is_array($patterns) ? $patterns : func_get_args();

        foreach ($patterns as $pattern) {
            if ($currentName !== null) {
                if ($currentName === $pattern) {
                    return true;
                }
                if (str_contains($pattern, '*')) {
                    $regex = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#i';
                    if (preg_match($regex, $currentName)) {
                        return true;
                    }
                }
            }

            $patternPath = trim($pattern, '/');
            $currentPath = trim($uri, '/');

            if ($patternPath === $currentPath) {
                return true;
            }

            if (str_contains($pattern, '*')) {
                $regex = '#^' . str_replace('\*', '.*', preg_quote($patternPath, '#')) . '$#i';
                if (preg_match($regex, $currentPath)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get all registered routes.
     */
    public static function getRoutes(): array
    {
        return self::$routes;
    }

    /**
     * Fallback handler when no route matches.
     */
    protected static mixed $fallbackHandler = null;

    /**
     * Register a GET route.
     */
    public static function get(string $path, mixed $handler): Route
    {
        return self::addRoute('GET', $path, $handler);
    }

    /**
     * Register a POST route.
     */
    public static function post(string $path, mixed $handler): Route
    {
        return self::addRoute('POST', $path, $handler);
    }

    /**
     * Register a PUT route.
     */
    public static function put(string $path, mixed $handler): Route
    {
        return self::addRoute('PUT', $path, $handler);
    }

    /**
     * Register a DELETE route.
     */
    public static function delete(string $path, mixed $handler): Route
    {
        return self::addRoute('DELETE', $path, $handler);
    }

    /**
     * Register a route responding to any HTTP method.
     */
    public static function any(string $path, mixed $handler): Route
    {
        return self::addRoute('*', $path, $handler);
    }

    /**
     * Set a fallback handler for 404 Not Found.
     */
    public static function fallback(mixed $handler): void
    {
        self::$fallbackHandler = $handler;
    }

    /**
     * Internal helper to register a route.
     */
    protected static function addRoute(string $method, string $path, mixed $handler): Route
    {
        $route = new Route($method, $path, $handler);
        self::$routes[] = $route;
        return $route;
    }

    /**
     * Add a named route to the registry.
     */
    public static function addNamedRoute(string $name, Route $route): void
    {
        self::$namedRoutes[$name] = $route;
    }

    /**
     * Generate URL for a named route.
     */
    public static function getUrlByName(string $name, array|string|int $params = []): string
    {
        if (!isset(self::$namedRoutes[$name])) {
            throw new Exception("Route with name [{$name}] is not defined in Routes/web.php.");
        }

        $path = self::$namedRoutes[$name]->getPath();

        // Parameter substitution e.g. /post/{id}
        if (!empty($params)) {
            if (!is_array($params)) {
                $params = ['id' => $params];
            }

            foreach ($params as $key => $val) {
                if (str_contains($path, '{' . $key . '}')) {
                    $path = str_replace('{' . $key . '}', (string) $val, $path);
                } else {
                    $path = preg_replace('/\{[a-zA-Z0-9_]+\}/', (string) $val, $path, 1);
                }
            }
        }

        return url($path);
    }

    /**
     * Dispatch the current request through registered routes.
     */
    public static function dispatch(): void
    {
        $uri = Request::uri();
        $method = Request::method();

        foreach (self::$routes as $route) {
            if ($route->getMethod() !== '*' && $route->getMethod() !== $method) {
                continue;
            }

            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route->getPath());
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                self::$currentRoute = $route;

                $result = self::executeHandler($route->getHandler(), $matches);

                if (is_string($result)) {
                    Response::html($result);
                }
                return;
            }
        }

        // No match found -> handle 404
        self::handleNotFound();
    }

    /**
     * Execute route handler (Closure, "Controller@method", or [Controller::class, 'method']).
     */
    protected static function executeHandler(mixed $handler, array $params = []): mixed
    {
        if ($handler instanceof Closure) {
            return call_user_func_array($handler, $params);
        }

        if (is_string($handler)) {
            // e.g. "HomeController@index"
            [$controllerName, $methodName] = explode('@', $handler);

            if (!str_contains($controllerName, '\\')) {
                $controllerClass = "Controller\\{$controllerName}";
            } else {
                $controllerClass = $controllerName;
            }

            if (!class_exists($controllerClass)) {
                throw new Exception("Controller class [{$controllerClass}] not found.");
            }

            $instance = new $controllerClass();

            if (!method_exists($instance, $methodName)) {
                throw new Exception("Method [{$methodName}] does not exist on controller [{$controllerClass}].");
            }

            return call_user_func_array([$instance, $methodName], $params);
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $instance = is_object($class) ? $class : new $class();
            return call_user_func_array([$instance, $method], $params);
        }

        throw new Exception("Invalid route handler provided.");
    }

    /**
     * Handle 404 Not Found.
     */
    protected static function handleNotFound(): void
    {
        if (self::$fallbackHandler) {
            Response::status(404);
            $result = self::executeHandler(self::$fallbackHandler);
            if (is_string($result)) {
                echo $result;
            }
            return;
        }

        // Automatically renders Pages/errors/404.srin.php if it exists
        Response::renderError(404, 'The page you requested could not be found.');
    }
}

