<?php

namespace Framework\Routing;

use Framework\Http\Request;
use Framework\Http\Response;

class Route
{
    private string $method;
    private string $uri;
    /** @var callable */
    private $handler;
    private array $middleware;

    public function __construct(string $method, string $uri, callable $handler, array $middleware = [])
    {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->handler = $handler;
        $this->middleware = $middleware;
    }

    public function matches(Request $request): bool
    {
        return $request->method() === $this->method && $request->uri() === $this->uri;
    }

    public function run(Request $request, callable $next, array $context): Response
    {
        $middleware = $this->middleware;
        $handler = $this->handler;

        $pipeline = array_reduce(
            array_reverse($middleware),
            function (callable $nextMiddleware, callable $middleware) use ($context) {
                return function (Request $req) use ($nextMiddleware, $middleware, $context) {
                    return $middleware($req, $nextMiddleware, $context);
                };
            },
            function (Request $req) use ($handler, $context) {
                return $handler($req, $context);
            }
        );

        return $pipeline($request);
    }
}
