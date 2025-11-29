<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_media', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('order');
        });
    }

    public function down(): void
    {
        Schema::table('property_media', function (Blueprint $table) {
            $table->dropColumn('is_featured');
        });
    }
};
