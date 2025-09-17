<?php

namespace Framework\Routing;

use Framework\Http\Request;
use Framework\Http\Response;

class Router
{
    /** @var Route[] */
    private array $routes = [];
    /** @var array<string, callable> */
    private array $middleware = [];

    public function middleware(string $name, callable $middleware): void
    {
        $this->middleware[$name] = $middleware;
    }

    public function get(string $uri, callable $handler, array $middleware = []): void
    {
        $this->routes[] = new Route('GET', $uri, $handler, $this->resolveMiddleware($middleware));
    }

    /** @param string[] $names */
    private function resolveMiddleware(array $names): array
    {
        return array_map(function (string $name) {
            if (!isset($this->middleware[$name])) {
                throw new \InvalidArgumentException("Middleware '{$name}' is not registered.");
            }
            return $this->middleware[$name];
        }, $names);
    }

    public function dispatch(Request $request, array $context = []): Response
    {
        foreach ($this->routes as $route) {
            if ($route->matches($request)) {
                return $route->run($request, fn () => new Response('Not Found', 404), $context);
            }
        }

        return new Response('Not Found', 404);
    }
}
