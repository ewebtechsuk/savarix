<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contacts')) {
            Schema::create('contacts', function (Blueprint $table) {
                $table->id();
                $table->string('type');
                $table->string('name');
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('company')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('contacts', function (Blueprint $table) {
                if (! Schema::hasColumn('contacts', 'type')) {
                    $table->string('type')->after('id');
                }
                if (! Schema::hasColumn('contacts', 'name')) {
                    $table->string('name')->after('type');
                }
                if (! Schema::hasColumn('contacts', 'first_name')) {
                    $table->string('first_name')->nullable()->after('name');
                }
                if (! Schema::hasColumn('contacts', 'last_name')) {
                    $table->string('last_name')->nullable()->after('first_name');
                }
                if (! Schema::hasColumn('contacts', 'company')) {
                    $table->string('company')->nullable()->after('last_name');
                }
                if (! Schema::hasColumn('contacts', 'email')) {
                    $table->string('email')->nullable()->after('company');
                }
                if (! Schema::hasColumn('contacts', 'phone')) {
                    $table->string('phone')->nullable()->after('email');
                }
                if (! Schema::hasColumn('contacts', 'address')) {
                    $table->string('address')->nullable()->after('phone');
                }
                if (! Schema::hasColumn('contacts', 'notes')) {
                    $table->text('notes')->nullable()->after('address');
                }
                if (! Schema::hasColumn('contacts', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        if (! Schema::hasTable('property_media')) {
            Schema::create('property_media', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained()->onDelete('cascade');
                $table->string('file_path');
                $table->string('type');
                $table->unsignedInteger('order')->default(0);
                $table->timestamps();
            });
        } else {
            Schema::table('property_media', function (Blueprint $table) {
                if (! Schema::hasColumn('property_media', 'property_id')) {
                    $table->foreignId('property_id')->constrained()->onDelete('cascade');
                }
                if (! Schema::hasColumn('property_media', 'file_path')) {
                    $table->string('file_path');
                }
                if (! Schema::hasColumn('property_media', 'type')) {
                    $table->string('type');
                }
                if (! Schema::hasColumn('property_media', 'order')) {
                    $table->unsignedInteger('order')->default(0);
                }
                if (! Schema::hasColumn('property_media', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down(): void
    {
        // This migration only ensures schema correctness; it does not drop tables to avoid data loss.
    }
};
