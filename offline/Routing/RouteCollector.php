<?php

namespace Offline\Routing;

class RouteCollector
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $routes = [];

    private int $nextId = 1;

    /**
     * @var array<int, array{prefix:string,name_prefix:string,middleware:array<int,string> }>
     */
    private array $contextStack;

    public function __construct()
    {
        $this->contextStack = [[
            'prefix' => '',
            'name_prefix' => '',
            'middleware' => [],
        ]];
    }

    public function addRoute(array $methods, string $uri, string $action, array $options = []): RouteDefinition
    {
        $context = $this->currentContext();
        $id = $this->nextId++;

        $route = [
            'id' => $id,
            'methods' => $this->normalizeMethods($methods),
            'uri' => $this->normalizeUri($context['prefix'], $uri),
            'name' => $this->applyNamePrefix($context['name_prefix'], $options['name'] ?? null),
            'action' => $action,
            'middleware' => $context['middleware'],
            'context_name_prefix' => $context['name_prefix'],
        ];

        $this->routes[$id] = $route;

        return new RouteDefinition($this, $id);
    }

    public function registerResource(string $resource, string $controller, bool $api = false, array $options = []): ResourceRegistration
    {
        $registration = new ResourceRegistration($this, $resource, $controller, $api);
        $registration->registerDefaults();

        if (isset($options['only'])) {
            $registration->only((array) $options['only']);
        }

        if (isset($options['except'])) {
            $registration->except((array) $options['except']);
        }

        if (isset($options['names'])) {
            $registration->names($options['names']);
        }

        if (isset($options['parameters'])) {
            $registration->parameters($options['parameters']);
        }

        return $registration;
    }

    public function withGroup(array $modifiers, callable $callback): void
    {
        $current = $this->currentContext();

        $next = [
            'prefix' => $this->mergePrefix($current['prefix'], $modifiers['prefix'] ?? ''),
            'name_prefix' => $current['name_prefix'] . ($modifiers['name_prefix'] ?? ''),
            'middleware' => $this->mergeMiddleware($current['middleware'], $modifiers['middleware'] ?? []),
        ];

        $this->contextStack[] = $next;
        try {
            $callback();
        } finally {
            array_pop($this->contextStack);
        }
    }

    public function updateRoute(int $id, callable $mutator): void
    {
        if (!isset($this->routes[$id])) {
            return;
        }

        $route = $this->routes[$id];
        $mutator($route);
        $this->routes[$id] = $route;
    }

    public function removeRoute(int $id): void
    {
        unset($this->routes[$id]);
    }

    public function appendMiddleware(int $id, array $middleware): void
    {
        if (!isset($this->routes[$id]) || $middleware === []) {
            return;
        }

        $existing = $this->routes[$id]['middleware'];
        $this->routes[$id]['middleware'] = $this->mergeMiddleware($existing, $middleware);
    }

    public function setRouteName(int $id, ?string $name): void
    {
        if (!isset($this->routes[$id])) {
            return;
        }

        $contextPrefix = $this->routes[$id]['context_name_prefix'];
        $this->routes[$id]['name'] = $this->applyNamePrefix($contextPrefix, $name);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRoutes(): array
    {
        return array_values($this->routes);
    }

    public function normalizeMiddlewareInput(mixed $middleware): array
    {
        return $this->normalizeMiddleware($middleware);
    }

    public function applyNamePrefix(string $prefix, ?string $name): ?string
    {
        $prefix = trim($prefix);

        if ($name === null || $name === '') {
            return $prefix !== '' ? rtrim($prefix, '.') : null;
        }

        return $prefix !== '' ? $prefix . $name : $name;
    }

    private function currentContext(): array
    {
        return $this->contextStack[count($this->contextStack) - 1];
    }

    private function normalizeMethods(array $methods): array
    {
        $seen = [];
        $normalized = [];
        foreach ($methods as $method) {
            $upper = strtoupper((string) $method);
            if (!isset($seen[$upper])) {
                $seen[$upper] = true;
                $normalized[] = $upper;
            }
        }

        return $normalized;
    }

    private function mergePrefix(string $base, string $addition): string
    {
        $base = trim($base, '/');
        $addition = trim($addition, '/');

        if ($base === '') {
            return $addition;
        }

        if ($addition === '') {
            return $base;
        }

        return $base . '/' . $addition;
    }

    private function normalizeUri(string $prefix, string $uri): string
    {
        $prefix = trim($prefix, '/');
        $uri = trim($uri, '/');

        $segments = [];
        if ($prefix !== '') {
            $segments[] = $prefix;
        }
        if ($uri !== '') {
            $segments[] = $uri;
        }

        $path = implode('/', $segments);

        return $path === '' ? '/' : $path;
    }

    /**
     * @param array<int, string> $base
     * @param mixed $additional
     * @return array<int, string>
     */
    private function mergeMiddleware(array $base, mixed $additional): array
    {
        $normalized = $this->normalizeMiddleware($additional);
        if ($normalized === []) {
            return $base;
        }

        $seen = [];
        $merged = [];

        foreach ([$base, $normalized] as $list) {
            foreach ($list as $entry) {
                if (!isset($seen[$entry])) {
                    $seen[$entry] = true;
                    $merged[] = $entry;
                }
            }
        }

        return $merged;
    }

    private function normalizeMiddleware(mixed $middleware): array
    {
        if ($middleware === null || $middleware === false) {
            return [];
        }

        if ($middleware instanceof \Traversable) {
            $middleware = iterator_to_array($middleware);
        }

        if (is_string($middleware)) {
            $middleware = [$middleware];
        }

        if (!is_array($middleware)) {
            return [];
        }

        $normalized = [];
        foreach ($middleware as $entry) {
            if ($entry === null || $entry === '') {
                continue;
            }
            $normalized[] = (string) $entry;
        }

        $seen = [];
        $result = [];
        foreach ($normalized as $value) {
            if (!isset($seen[$value])) {
                $seen[$value] = true;
                $result[] = $value;
            }
        }

        return $result;
    }
}
