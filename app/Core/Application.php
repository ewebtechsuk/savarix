<?php

namespace App\Core;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Routing\Router;

class Application
{
    private Router $router;
    private string $viewPath;

    public function __construct(string $basePath)
    {
        $this->router = new Router();
        $this->viewPath = rtrim($basePath, '/') . '/resources/views';
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function view(string $name, array $data = []): string
    {
        $path = $this->viewPath . '/' . str_replace('.', '/', $name) . '.php';
        if (!is_file($path)) {
            throw new \RuntimeException("View '{$name}' not found.");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $path;
        return (string) ob_get_clean();
    }

    public function handle(string $method, string $uri): Response
    {
        $request = new Request($method, $uri);
        return $this->router->dispatch($request, ['app' => $this]);
    }
}
