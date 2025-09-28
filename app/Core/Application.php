<?php

namespace App\Core;

use App\Auth\AuthManager;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Routing\Router;
use Illuminate\Database\ConnectionInterface;

class Application
{
    private Router $router;
    private AuthManager $auth;
    private string $viewPath;
    private ?ConnectionInterface $database = null;

    public function __construct(string $basePath)
    {
        $this->router = new Router();
        $this->auth = new AuthManager();
        $this->viewPath = rtrim($basePath, '/') . '/resources/views';
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function auth(): AuthManager
    {
        return $this->auth;
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

    public function setDatabaseConnection(ConnectionInterface $connection): void
    {
        $this->database = $connection;
    }

    public function database(): ?ConnectionInterface
    {
        return $this->database;
    }

    public function handle(string $method, string $uri): Response
    {
        $request = new Request($method, $uri);
        return $this->router->dispatch($request, ['app' => $this]);
    }
}
