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
        $connection = $this->connectionName();
        $schema = Schema::connection($connection);

        if (! $schema->hasTable('agencies')) {
            $schema->create('agencies', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('users')) {
            return;
        }

        $schema->table('users', function (Blueprint $table) use ($schema): void {
            if (! $schema->hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }

            if (! $schema->hasColumn('users', 'role')) {
                $table->string('role')->default('agent')->after('email');
            }

            if (! $schema->hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('password');
            }

            if (! $schema->hasColumn('users', 'login_token')) {
                $table->string('login_token', 64)->nullable()->unique();
            }

            if (! $schema->hasColumn('users', 'agency_id')) {
                $table->unsignedBigInteger('agency_id')->nullable()->after('id');

                if ($schema->hasTable('agencies')) {
                    $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
                }
            }
        });
    }

    public function down(): void
    {
        $connection = $this->connectionName();
        $schema = Schema::connection($connection);

        if (! $schema->hasTable('users')) {
            return;
        }

        $schema->table('users', function (Blueprint $table) use ($schema): void {
            if ($schema->hasColumn('users', 'agency_id')) {
                $table->dropForeign(['agency_id']);
                $table->dropColumn('agency_id');
            }

            if ($schema->hasColumn('users', 'login_token')) {
                $table->dropColumn('login_token');
            }

            if ($schema->hasColumn('users', 'is_admin')) {
                $table->dropColumn('is_admin');
            }

            if ($schema->hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }

            if ($schema->hasColumn('users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });

        if ($schema->hasTable('agencies')) {
            $schema->dropIfExists('agencies');
        }
    }
};
