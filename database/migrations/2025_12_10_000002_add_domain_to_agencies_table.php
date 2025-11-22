<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected function connectionName(): string
    {
        return config('tenancy.database.central_connection', config('database.default'));
    }

    public function up(): void
    {
        $schema = Schema::connection($this->connectionName());

        if (! $schema->hasTable('agencies')) {
            $schema->create('agencies', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('status')->default('active');
                $table->string('domain')->nullable()->unique();
                $table->timestamps();
            });

            return;
        }

        if (! $schema->hasColumn('agencies', 'domain')) {
            $schema->table('agencies', function (Blueprint $table): void {
                $table->string('domain')->nullable()->unique()->after('phone');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connectionName());

        if (! $schema->hasTable('agencies')) {
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
