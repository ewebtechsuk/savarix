<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contacts')) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            if (! Schema::hasColumn('contacts', 'company')) {
                $table->string('company')->nullable()->after('last_name');
            }

            if (! Schema::hasColumn('contacts', 'company_id')) {
                $table->string('company_id', 10)->nullable()->after('company');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('contacts')) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            if (Schema::hasColumn('contacts', 'company_id')) {
                $table->dropColumn('company_id');
            }

            if (Schema::hasColumn('contacts', 'company')) {
                $table->dropColumn('company');
            }
        });
    }
};
