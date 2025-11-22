<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Throwable;

return new class extends Migration {
    protected function connectionName(): string
    {
        return config('tenancy.database.central_connection', config('database.default'));
    }

    public function up(): void
    {
        $schema = Schema::connection($this->connectionName());

        if (! $schema->hasTable('agencies') || $schema->hasColumn('agencies', 'domain')) {
            return;
        }

        $schema->table('agencies', function (Blueprint $table): void {
            $table->string('domain')->nullable()->unique()->after('phone');
        });
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connectionName());

        if (! $schema->hasTable('agencies') || ! $schema->hasColumn('agencies', 'domain')) {
            return;
        }

        $connection = $schema->getConnection();
        $hasUniqueIndex = false;

        try {
            $hasUniqueIndex = count($connection->select(
                'SHOW INDEX FROM agencies WHERE Column_name = ? AND Non_unique = 0',
                ['domain']
            )) > 0;
        } catch (Throwable $exception) {
            // Ignore index introspection failures; continue with the drop.
        }

        $schema->table('agencies', function (Blueprint $table) use ($hasUniqueIndex): void {
            if ($hasUniqueIndex) {
                $table->dropUnique('agencies_domain_unique');
            }

            $table->dropColumn('domain');
        });
    }
};
