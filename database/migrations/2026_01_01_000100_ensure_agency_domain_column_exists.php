<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private static bool $createdColumn = false;

    protected function connectionName(): string
    {
        return config('tenancy.database.central_connection', config('database.default'));
    }

    public function up(): void
    {
        $schema = Schema::connection($this->connectionName());

        if (! $schema->hasTable('agencies')) {
            self::$createdColumn = false;

            return;
        }

        $columnExisted = $schema->hasColumn('agencies', 'domain');

        $schema->table('agencies', function (Blueprint $table) use ($columnExisted): void {
            if (! $columnExisted) {
                $table->string('domain')->nullable()->unique()->after('phone');
            }
        });

        self::$createdColumn = ! $columnExisted;
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connectionName());

        if (! self::$createdColumn || ! $schema->hasTable('agencies')) {
            return;
        }

        $schema->table('agencies', function (Blueprint $table) use ($schema): void {
            if ($schema->hasColumn('agencies', 'domain')) {
                $table->dropUnique('agencies_domain_unique');
                $table->dropColumn('domain');
            }
        });
    }
};
