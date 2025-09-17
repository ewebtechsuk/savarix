<?php

namespace Tests;

use App\Core\Application;
use Framework\Http\Response;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected Application $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = $this->createApplication();
    }

    protected function get(string $uri): Response
    {
        return $this->app->handle('GET', $uri);
    }

    protected function actingAs($user): self
    {
        $this->app->auth()->login($user);
        return $this;
    }

    protected function assertStatus(Response $response, int $expected): void
    {
        self::assertSame($expected, $response->status());
    }

    protected function assertRedirect(Response $response, string $location): void
    {
        $this->assertStatus($response, 302);
        self::assertSame($location, $response->header('location'));
    }

    protected function assertSee(Response $response, string $text): void
    {
        self::assertStringContainsString($text, $response->body());
    }
}
