<?php

namespace Offline\Routing;

class RouteGroupBuilder
{
    private string $prefix = '';

    private string $namePrefix = '';

    /**
     * @var array<int, string>
     */
    private array $middleware = [];

    public function __construct(private readonly RouteCollector $collector)
    {
    }

    public function middleware(mixed $middleware): self
    {
        $this->middleware = $this->collector->normalizeMiddlewareInput($this->middleware);
        $additional = $this->collector->normalizeMiddlewareInput($middleware);
        $this->middleware = array_values(array_unique(array_merge($this->middleware, $additional)));

        return $this;
    }

    public function name(string $prefix): self
    {
        $this->namePrefix .= $prefix;

        return $this;
    }

    public function prefix(string $prefix): self
    {
        if ($this->prefix === '') {
            $this->prefix = trim($prefix, '/');
        } else {
            $this->prefix = trim($this->prefix . '/' . trim($prefix, '/'), '/');
        }

        return $this;
    }

    public function group(callable $callback): void
    {
        $this->collector->withGroup([
            'prefix' => $this->prefix,
            'name_prefix' => $this->namePrefix,
            'middleware' => $this->middleware,
        ], $callback);
    }
}
