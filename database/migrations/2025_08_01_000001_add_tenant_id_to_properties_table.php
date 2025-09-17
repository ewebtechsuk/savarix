<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('properties', 'tenant_id')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('properties', 'tenant_id')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }
    }
};
