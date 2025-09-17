<?php

namespace Offline\Routing;

class RouteDefinition
{
    public function __construct(
        private readonly RouteCollector $collector,
        private readonly int $routeId
    ) {
    }

    public function name(string $name): self
    {
        $this->collector->setRouteName($this->routeId, $name);

        return $this;
    }

    public function middleware(mixed $middleware): self
    {
        $normalized = $this->collector->normalizeMiddlewareInput($middleware);
        $this->collector->appendMiddleware($this->routeId, $normalized);

        return $this;
    }

    public function where(mixed $conditions): self
    {
        // Parameter constraints are not required for offline route listing.
        return $this;
    }

    public function id(): int
    {
        return $this->routeId;
    }
}
