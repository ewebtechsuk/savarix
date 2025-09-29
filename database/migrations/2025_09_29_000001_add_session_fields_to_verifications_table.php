<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('verifications', function (Blueprint $table) {
            $table->string('provider_session_url')->nullable()->after('provider_reference');
            $table->json('session_metadata')->nullable()->after('provider_session_url');
            $table->string('error_code')->nullable()->after('session_metadata');
            $table->text('error_message')->nullable()->after('error_code');
        });
    }

    public function down(): void
    {
        Schema::table('verifications', function (Blueprint $table) {
            $table->dropColumn(['provider_session_url', 'session_metadata', 'error_code', 'error_message']);
        });
    }
};
