<?php

namespace Core;

class Route
{
    protected string $method;
    protected string $path;
    protected mixed $handler;
    protected ?string $name = null;

    public function __construct(string $method, string $path, mixed $handler)
    {
        $this->method = strtoupper($method);
        $this->path = '/' . trim($path, '/');
        $this->handler = $handler;
    }

    /**
     * Assign a unique name to this route.
     * Example: Router::get('/about', 'HomeController@about')->name('about');
     */
    public function name(string $name): self
    {
        $this->name = $name;
        Router::addNamedRoute($name, $this);
        return $this;
    }

    /**
     * Get the HTTP method.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get the URL path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get the action handler.
     */
    public function getHandler(): mixed
    {
        return $this->handler;
    }

    /**
     * Get the route name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}

