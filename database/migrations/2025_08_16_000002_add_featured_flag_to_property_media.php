<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_media', function (Blueprint $table) {
            if (! Schema::hasColumn('property_media', 'is_featured')) {
                $column = $table->boolean('is_featured')->default(false);

                if (Schema::hasColumn('property_media', 'order')) {
                    $column->after('order');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('property_media', function (Blueprint $table) {
            if (Schema::hasColumn('property_media', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
        });
    }
};
