<?php

namespace Core;

class Route
{
    protected string $method;
    protected string $path;
    protected mixed $handler;
    protected ?string $name = null;

    /**
     * Checkpoints assigned to this route.
     * @var array<string|object>
     */
    protected array $checkpoints = [];

    public function __construct(string $method, string $path, mixed $handler)
    {
        $this->method = strtoupper($method);
        $this->path = '/' . trim($path, '/');
        $this->handler = $handler;
    }

    /**
     * Attach one or more checkpoints (gates) to this route.
     * Example: Router::get('/admin', 'Admin@index')->checkpoint('auth');
     * Example: Router::get('/reports', 'Report@index')->checkpoint(['auth', 'admin']);
     */
    public function checkpoint(string|array $checkpoints): self
    {
        $list = is_array($checkpoints) ? $checkpoints : func_get_args();
        foreach ($list as $cp) {
            $this->checkpoints[] = $cp;
        }
        return $this;
    }

    /**
     * Get checkpoints attached to this route.
     */
    public function getCheckpoints(): array
    {
        return $this->checkpoints;
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

