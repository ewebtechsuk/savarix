<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landlords', function (Blueprint $table) {
            if (! Schema::hasColumn('landlords', 'password')) {
                $table->string('password')->nullable()->after('contact_email');
            }
        });

        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'email')) {
                $table->string('email')->nullable()->after('id');
            }

            if (! Schema::hasColumn('tenants', 'password')) {
                $table->string('password')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('landlords', function (Blueprint $table) {
            if (Schema::hasColumn('landlords', 'password')) {
                $table->dropColumn('password');
            }
        });

        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'password')) {
                $table->dropColumn('password');
            }

            if (Schema::hasColumn('tenants', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
