<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('invoices', 'date')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->date('date')->nullable()->after('number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'date')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('date');
            });
        }
    }
};
