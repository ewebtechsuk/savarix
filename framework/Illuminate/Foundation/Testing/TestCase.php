<?php

namespace Illuminate\Foundation\Testing;

use App\Core\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = $this->createApplication();
    }

    protected function tearDown(): void
    {
        unset($this->app);

        parent::tearDown();
    }

    abstract protected function createApplication(): Application;

    protected function call(string $method, string $uri, array $data = [], array $headers = []): TestResponse
    {
        $response = $this->app->handle(strtoupper($method), $uri);

        return new TestResponse($response);
    }

    protected function get(string $uri, array $headers = []): TestResponse
    {
        return $this->call('GET', $uri, [], $headers);
    }

    protected function post(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->call('POST', $uri, $data, $headers);
    }

    protected function actingAs($user, string $guard = 'web'): static
    {
        Auth::guard($guard)->login($user);
        Auth::shouldUse($guard);

        return $this;
    }
}
