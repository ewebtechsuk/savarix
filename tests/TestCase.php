<?php

namespace Tests;

use App\Core\Application;
use App\Models\Tenant;
use App\Models\User;
use Framework\Http\Response;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected Application $app;

    private static ?Capsule $database = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootstrapDatabase();

        $this->app = $this->createApplication();

        User::truncate();
        Tenant::query()->delete();
        self::$database->table('domains')->delete();
        self::$database->table('landlords')->delete();

        Auth::shouldUse('web');
        Auth::guard('web')->logout();
        Auth::guard('tenant')->logout();
    }

    protected function get(string $uri): Response
    {
        return $this->app->handle('GET', $uri);
    }

    protected function actingAs($user, string $guard = 'web'): self
    {
        Auth::guard($guard)->login($user);
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

    private function bootstrapDatabase(): void
    {
        if (self::$database instanceof Capsule) {
            return;
        }

        $capsule = new Capsule();
        $capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $capsule->setEventDispatcher(new Dispatcher(new Container()));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $schema = $capsule->schema();

        $schema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_admin')->default(false);
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });

        $schema->create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->text('data')->nullable();
            $table->timestamps();
        });

        $schema->create('domains', function (Blueprint $table) {
            $table->increments('id');
            $table->string('domain')->unique();
            $table->string('tenant_id');
            $table->timestamps();
        });

        $schema->create('landlords', function (Blueprint $table) {
            $table->increments('id');
            $table->string('contact_email')->nullable();
            $table->string('password')->nullable();
            $table->string('person_firstname')->nullable();
            $table->string('person_lastname')->nullable();
            $table->tinyInteger('person_landlord')->nullable();
            $table->string('person_type')->nullable();
            $table->timestamps();
        });

        self::$database = $capsule;
    }
}
