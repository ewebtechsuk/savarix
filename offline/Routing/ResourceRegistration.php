<?php

namespace Offline\Routing;

class ResourceRegistration
{
    private const PARAMETER_PLACEHOLDER = '{parameter}';

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $actionMap;

    /**
     * @var array<string, int>
     */
    private array $routeIds = [];

    /**
     * @var array<string, string>
     */
    private array $parameterOverrides = [];

    public function __construct(
        private readonly RouteCollector $collector,
        private readonly string $resource,
        private readonly string $controller,
        private readonly bool $apiResource
    ) {
        $this->actionMap = $apiResource ? $this->apiActions() : $this->standardActions();
    }

    public function registerDefaults(): void
    {
        foreach ($this->actionMap as $action => $definition) {
            $route = $this->collector->addRoute(
                $definition['methods'],
                $this->resourceUri($definition['suffix']),
                $this->formatAction($definition['controller_method']),
                ['name' => $this->defaultRouteName($action)]
            );

            $this->routeIds[$action] = $route->id();
        }
    }

    public function only(array $actions): self
    {
        $allowed = array_map('strval', $actions);
        foreach ($this->routeIds as $action => $routeId) {
            if (!in_array($action, $allowed, true)) {
                $this->collector->removeRoute($routeId);
                unset($this->routeIds[$action]);
            }
        }

        return $this;
    }

    public function except(array $actions): self
    {
        $blocked = array_map('strval', $actions);
        foreach ($blocked as $action) {
            if (isset($this->routeIds[$action])) {
                $this->collector->removeRoute($this->routeIds[$action]);
                unset($this->routeIds[$action]);
            }
        }

        return $this;
    }

    public function names(mixed $names): self
    {
        if (is_array($names)) {
            foreach ($names as $action => $name) {
                if (isset($this->routeIds[$action])) {
                    $this->collector->setRouteName($this->routeIds[$action], (string) $name);
                }
            }

            return $this;
        }

        if (is_string($names) && $names !== '') {
            $prefix = rtrim($names, '.') . '.';
            foreach ($this->routeIds as $action => $routeId) {
                $this->collector->setRouteName($routeId, $prefix . $action);
            }
        }

        return $this;
    }

    public function parameters(array $parameters): self
    {
        // Parameter overrides are not required for the offline route view.
        $this->parameterOverrides = $parameters + $this->parameterOverrides;

        return $this;
    }

    private function defaultRouteName(string $action): string
    {
        return $this->resource . '.' . $action;
    }

    private function formatAction(string $method): string
    {
        if (str_contains($method, '@')) {
            return $method;
        }

        return $this->controller . '@' . $method;
    }

    private function resourceUri(string $suffix): string
    {
        $base = trim($this->resource, '/');
        $suffix = str_replace(self::PARAMETER_PLACEHOLDER, $this->parameterSegment(), $suffix);
        $suffix = trim($suffix, '/');

        $segments = [];
        if ($base !== '') {
            $segments[] = $base;
        }
        if ($suffix !== '') {
            $segments[] = $suffix;
        }

        $path = implode('/', $segments);

        return $path === '' ? '/' : $path;
    }

    private function parameterSegment(): string
    {
        if (isset($this->parameterOverrides[$this->resource])) {
            return '{' . $this->parameterOverrides[$this->resource] . '}';
        }

        $name = $this->resource;
        if (str_ends_with($name, 'ies')) {
            $name = substr($name, 0, -3) . 'y';
        } elseif (str_ends_with($name, 'ses')) {
            $name = substr($name, 0, -2);
        } elseif (str_ends_with($name, 's')) {
            $name = substr($name, 0, -1);
        }

        return '{' . $name . '}';
    }

    /**
     * @return array<string, array{methods:array<int,string>,suffix:string,controller_method:string}>
     */
    private function standardActions(): array
    {
        return [
            'index' => ['methods' => ['GET'], 'suffix' => '', 'controller_method' => 'index'],
            'create' => ['methods' => ['GET'], 'suffix' => 'create', 'controller_method' => 'create'],
            'store' => ['methods' => ['POST'], 'suffix' => '', 'controller_method' => 'store'],
            'show' => ['methods' => ['GET'], 'suffix' => self::PARAMETER_PLACEHOLDER, 'controller_method' => 'show'],
            'edit' => ['methods' => ['GET'], 'suffix' => self::PARAMETER_PLACEHOLDER . '/edit', 'controller_method' => 'edit'],
            'update' => ['methods' => ['PUT', 'PATCH'], 'suffix' => self::PARAMETER_PLACEHOLDER, 'controller_method' => 'update'],
            'destroy' => ['methods' => ['DELETE'], 'suffix' => self::PARAMETER_PLACEHOLDER, 'controller_method' => 'destroy'],
        ];
    }

    /**
     * @return array<string, array{methods:array<int,string>,suffix:string,controller_method:string}>
     */
    private function apiActions(): array
    {
        return [
            'index' => ['methods' => ['GET'], 'suffix' => '', 'controller_method' => 'index'],
            'store' => ['methods' => ['POST'], 'suffix' => '', 'controller_method' => 'store'],
            'show' => ['methods' => ['GET'], 'suffix' => self::PARAMETER_PLACEHOLDER, 'controller_method' => 'show'],
            'update' => ['methods' => ['PUT', 'PATCH'], 'suffix' => self::PARAMETER_PLACEHOLDER, 'controller_method' => 'update'],
            'destroy' => ['methods' => ['DELETE'], 'suffix' => self::PARAMETER_PLACEHOLDER, 'controller_method' => 'destroy'],
        ];
    }
}
