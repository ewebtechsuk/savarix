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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->default('available');
            $table->foreignId('vendor_id')->nullable()->constrained('contacts');
            $table->foreignId('landlord_id')->nullable()->constrained('contacts');
            $table->foreignId('applicant_id')->nullable()->constrained('contacts');
            $table->text('notes')->nullable();
            $table->json('activity_log')->nullable();
            $table->string('document')->nullable(); // for file upload
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
