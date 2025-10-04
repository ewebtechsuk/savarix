<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;


abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', database_path('database.sqlite'));

        if (! file_exists(database_path('database.sqlite'))) {
            touch(database_path('database.sqlite'));
        }

        $migrationResult = Artisan::call('migrate:fresh', ['--database' => 'sqlite', '--force' => true]);

        if ($migrationResult !== 0) {
            throw new \RuntimeException('Failed to migrate testing database: ' . Artisan::output());
        }
    }
}
