<?php

namespace Tests;

use App\Core\Application as LegacyApplication;
use App\Models\User;
use Framework\Http\Response;
use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Tests\Support\LegacyTestResponse;

$hasModernLaravel = class_exists(\Illuminate\Foundation\Application::class)
    && version_compare(\Illuminate\Foundation\Application::VERSION, '10.0.0', '>=');

if ($hasModernLaravel && class_exists(LaravelTestCase::class)) {
    abstract class TestCase extends LaravelTestCase
    {
        use CreatesApplication;

        protected function setUp(): void
        {
            parent::setUp();

            if (method_exists($this, 'withoutVite')) {
                $this->withoutVite();
            }
        }
    }
} else {
    abstract class TestCase extends PHPUnitTestCase
    {
        use CreatesApplication;

        protected LegacyApplication $app;

        protected function setUp(): void
        {
            parent::setUp();

            require_once __DIR__.'/Support/helpers.php';

            $this->app = $this->createApplication();

            User::truncate();
            Auth::shouldUse('web');
            Auth::guard('web')->logout();
            Auth::guard('tenant')->logout();
        }

        protected function get(string $uri): LegacyTestResponse
        {
            $response = $this->app->handle('GET', $uri);

            return new LegacyTestResponse($response, $this);
        }

        protected function actingAs($user, string $guard = 'web'): static
        {
            Auth::guard($guard)->login($user);

            return $this;
        }
    }
}
