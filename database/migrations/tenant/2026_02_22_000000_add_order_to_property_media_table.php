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
        if (! Schema::hasTable('property_media')) {
            return;
        }

        if (Schema::hasColumn('property_media', 'order')) {
            return;
        }

        Schema::table('property_media', function (Blueprint $table) {
            $table->unsignedInteger('order')->default(0)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('property_media')) {
            return;
        }

        if (! Schema::hasColumn('property_media', 'order')) {
            return;
        }

        Schema::table('property_media', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
