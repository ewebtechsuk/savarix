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
        if (! Schema::hasTable('contacts')) {
            return;
        }

        if (Schema::hasColumn('contacts', 'company')) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            $table->string('company')->nullable()->after('last_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('contacts')) {
            return;
        }

        if (! Schema::hasColumn('contacts', 'company')) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('company');
        });
    }
};
