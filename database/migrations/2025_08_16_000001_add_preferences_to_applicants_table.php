<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->decimal('min_budget', 12, 2)->nullable()->after('status');
            $table->decimal('max_budget', 12, 2)->nullable()->after('min_budget');
            $table->unsignedInteger('preferred_bedrooms')->nullable()->after('max_budget');
            $table->string('preferred_city')->nullable()->after('preferred_bedrooms');
            $table->boolean('marketing_opt_in')->default(true)->after('preferred_city');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn([
                'min_budget',
                'max_budget',
                'preferred_bedrooms',
                'preferred_city',
                'marketing_opt_in',
            ]);
        });
    }
};
