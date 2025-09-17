<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diary_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('date')->nullable();
            $table->dateTime('start');
            $table->dateTime('end')->nullable();
            $table->string('type'); // appointment, viewing, inspection, etc
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('property_id')->nullable()->constrained('properties');
            $table->foreignId('contact_id')->nullable()->constrained('contacts');
            $table->string('color')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('diary_events');
    }
};
