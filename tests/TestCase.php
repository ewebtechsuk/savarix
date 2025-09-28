<?php

namespace Tests;

use App\Core\Application;
use Database\Seeders\TenantSeeder;
use Framework\Http\Response;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected Application $app;
    protected static ?Capsule $capsule = null;
    protected static bool $tenancySchemaMigrated = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = $this->createApplication();
        $this->bootTenancyDatabase();
        $this->seedTenancyFixtures();
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

    private function bootTenancyDatabase(): void
    {
        $depsAutoload = dirname(__DIR__) . '/deps/vendor/autoload.php';
        if (file_exists($depsAutoload)) {
            require_once $depsAutoload;
        }

        if (!static::$capsule) {
            $capsule = new Capsule();
            $capsule->addConnection([
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            static::$capsule = $capsule;

            // Keep the SQLite in-memory connection alive across tests.
            static::$capsule->getConnection()->getPdo();
        }

        $this->app->setDatabaseConnection(static::$capsule->getConnection());

        if (!static::$tenancySchemaMigrated) {
            $schema = static::$capsule->schema();

            if (! $schema->hasTable('tenants')) {
                $schema->create('tenants', function (Blueprint $table) {
                    $table->string('id')->primary();
                    $table->timestamps();
                    $table->json('data')->nullable();
                });
            }

            if (! $schema->hasTable('domains')) {
                $schema->create('domains', function (Blueprint $table) {
                    $table->increments('id');
                    $table->string('domain', 255)->unique();
                    $table->string('tenant_id');
                    $table->timestamps();
                });
            }

            static::$tenancySchemaMigrated = true;
        }
    }

    private function seedTenancyFixtures(): void
    {
        if (!static::$capsule) {
            return;
        }

        $connection = static::$capsule->getConnection();
        $connection->table('domains')->delete();
        $connection->table('tenants')->delete();

        $seeder = new TenantSeeder();
        $seeder->run();
    }
}
