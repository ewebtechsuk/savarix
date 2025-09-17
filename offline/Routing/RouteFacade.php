<?php

namespace Offline\Routing;

class RouteFacade
{
    private static ?RouteCollector $collector = null;

    public static function setCollector(RouteCollector $collector): void
    {
        self::$collector = $collector;
    }

    public static function getCollector(): RouteCollector
    {
        if (self::$collector === null) {
            self::$collector = new RouteCollector();
        }

        return self::$collector;
    }

    public static function get(string $uri, mixed $action = null): RouteDefinition
    {
        return self::addRoute(['GET'], $uri, $action);
    }

    public static function post(string $uri, mixed $action = null): RouteDefinition
    {
        return self::addRoute(['POST'], $uri, $action);
    }

    public static function put(string $uri, mixed $action = null): RouteDefinition
    {
        return self::addRoute(['PUT'], $uri, $action);
    }

    public static function patch(string $uri, mixed $action = null): RouteDefinition
    {
        return self::addRoute(['PATCH'], $uri, $action);
    }

    public static function delete(string $uri, mixed $action = null): RouteDefinition
    {
        return self::addRoute(['DELETE'], $uri, $action);
    }

    public static function options(string $uri, mixed $action = null): RouteDefinition
    {
        return self::addRoute(['OPTIONS'], $uri, $action);
    }

    public static function match(array $methods, string $uri, mixed $action = null): RouteDefinition
    {
        return self::addRoute($methods, $uri, $action);
    }

    public static function any(string $uri, mixed $action = null): RouteDefinition
    {
        return self::addRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $uri, $action);
    }

    public static function middleware(mixed $middleware): RouteGroupBuilder
    {
        $builder = new RouteGroupBuilder(self::getCollector());
        if ($middleware !== null) {
            $builder->middleware($middleware);
        }

        return $builder;
    }

    public static function prefix(string $prefix): RouteGroupBuilder
    {
        $builder = new RouteGroupBuilder(self::getCollector());
        return $builder->prefix($prefix);
    }

    public static function name(string $prefix): RouteGroupBuilder
    {
        $builder = new RouteGroupBuilder(self::getCollector());
        return $builder->name($prefix);
    }

    public static function group(array $attributes, callable $callback): void
    {
        $builder = new RouteGroupBuilder(self::getCollector());

        if (isset($attributes['middleware'])) {
            $builder->middleware($attributes['middleware']);
        }

        if (isset($attributes['prefix'])) {
            $builder->prefix($attributes['prefix']);
        }

        if (isset($attributes['as'])) {
            $builder->name($attributes['as']);
        }

        $builder->group($callback);
    }

    public static function resource(string $name, string $controller, array $options = []): ResourceRegistration
    {
        return self::getCollector()->registerResource($name, $controller, false, $options);
    }

    public static function apiResource(string $name, string $controller, array $options = []): ResourceRegistration
    {
        return self::getCollector()->registerResource($name, $controller, true, $options);
    }

    public static function apiResources(array $resources): void
    {
        foreach ($resources as $name => $controller) {
            self::apiResource($name, $controller);
        }
    }

    public static function resources(array $resources): void
    {
        foreach ($resources as $name => $controller) {
            self::resource($name, $controller);
        }
    }

    private static function addRoute(array $methods, string $uri, mixed $action = null): RouteDefinition
    {
        $collector = self::getCollector();
        $callable = self::formatAction($action);

        return $collector->addRoute($methods, $uri, $callable);
    }

    private static function formatAction(mixed $action): string
    {
        if ($action === null) {
            return 'Closure';
        }

        if ($action instanceof \Closure) {
            return 'Closure';
        }

        if (is_array($action) && count($action) === 2) {
            [$class, $method] = $action;
            $className = is_object($class) ? get_class($class) : (string) $class;

            return $className . '@' . $method;
        }

        if (is_string($action)) {
            return $action;
        }

        if (is_object($action)) {
            return get_class($action);
        }

        return 'Closure';
    }
}

namespace Illuminate\Support\Facades;

class Route
{
    public static function __callStatic(string $name, array $arguments)
    {
        return \call_user_func_array([\Offline\Routing\RouteFacade::class, $name], $arguments);
    }
}
