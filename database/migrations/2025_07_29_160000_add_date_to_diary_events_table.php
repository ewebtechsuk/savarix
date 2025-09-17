<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('diary_events', 'date')) {
            Schema::table('diary_events', function (Blueprint $table) {
                $table->dateTime('date')->nullable()->after('description');
            });
        }
    }
    public function down(): void
    {
        if (Schema::hasColumn('diary_events', 'date')) {
            Schema::table('diary_events', function (Blueprint $table) {
                $table->dropColumn('date');
            });
        }
    }
};
