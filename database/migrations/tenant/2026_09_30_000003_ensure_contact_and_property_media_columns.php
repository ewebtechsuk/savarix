<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contacts')) {
            Schema::create('contacts', function (Blueprint $table) {
                $table->id();
                $table->string('type');
                $table->string('name');
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('company')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('contacts', function (Blueprint $table) {
                if (! Schema::hasColumn('contacts', 'type')) {
                    $table->string('type')->after('id');
                }
                if (! Schema::hasColumn('contacts', 'name')) {
                    $table->string('name')->after('type');
                }
                if (! Schema::hasColumn('contacts', 'first_name')) {
                    $table->string('first_name')->nullable()->after('name');
                }
                if (! Schema::hasColumn('contacts', 'last_name')) {
                    $table->string('last_name')->nullable()->after('first_name');
                }
                if (! Schema::hasColumn('contacts', 'company')) {
                    $table->string('company')->nullable()->after('last_name');
                }
                if (! Schema::hasColumn('contacts', 'email')) {
                    $table->string('email')->nullable()->after('company');
                }
                if (! Schema::hasColumn('contacts', 'phone')) {
                    $table->string('phone')->nullable()->after('email');
                }
                if (! Schema::hasColumn('contacts', 'address')) {
                    $table->string('address')->nullable()->after('phone');
                }
                if (! Schema::hasColumn('contacts', 'notes')) {
                    $table->text('notes')->nullable()->after('address');
                }
                if (! Schema::hasColumn('contacts', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        if (! Schema::hasTable('properties')) {
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
                $table->string('document')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('properties', function (Blueprint $table) {
                if (! Schema::hasColumn('properties', 'title')) {
                    $table->string('title');
                }
                if (! Schema::hasColumn('properties', 'description')) {
                    $table->text('description')->nullable();
                }
                if (! Schema::hasColumn('properties', 'price')) {
                    $table->decimal('price', 12, 2)->nullable();
                }
                if (! Schema::hasColumn('properties', 'address')) {
                    $table->string('address')->nullable();
                }
                if (! Schema::hasColumn('properties', 'city')) {
                    $table->string('city')->nullable();
                }
                if (! Schema::hasColumn('properties', 'postcode')) {
                    $table->string('postcode')->nullable();
                }
                if (! Schema::hasColumn('properties', 'bedrooms')) {
                    $table->integer('bedrooms')->nullable();
                }
                if (! Schema::hasColumn('properties', 'bathrooms')) {
                    $table->integer('bathrooms')->nullable();
                }
                if (! Schema::hasColumn('properties', 'type')) {
                    $table->string('type')->nullable();
                }
                if (! Schema::hasColumn('properties', 'status')) {
                    $table->string('status')->default('available');
                }
                if (! Schema::hasColumn('properties', 'vendor_id')) {
                    $table->foreignId('vendor_id')->nullable()->constrained('contacts');
                }
                if (! Schema::hasColumn('properties', 'landlord_id')) {
                    $table->foreignId('landlord_id')->nullable()->constrained('contacts');
                }
                if (! Schema::hasColumn('properties', 'applicant_id')) {
                    $table->foreignId('applicant_id')->nullable()->constrained('contacts');
                }
                if (! Schema::hasColumn('properties', 'notes')) {
                    $table->text('notes')->nullable();
                }
                if (! Schema::hasColumn('properties', 'activity_log')) {
                    $table->json('activity_log')->nullable();
                }
                if (! Schema::hasColumn('properties', 'document')) {
                    $table->string('document')->nullable();
                }
                if (! Schema::hasColumn('properties', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        if (! Schema::hasTable('property_media')) {
            Schema::create('property_media', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained()->onDelete('cascade');
                $table->string('file_path');
                $table->string('type');
                $table->unsignedInteger('order')->default(0);
                $table->timestamps();
            });
        } else {
            Schema::table('property_media', function (Blueprint $table) {
                if (! Schema::hasColumn('property_media', 'property_id')) {
                    $table->foreignId('property_id')->constrained()->onDelete('cascade');
                }
                if (! Schema::hasColumn('property_media', 'file_path')) {
                    $table->string('file_path');
                }
                if (! Schema::hasColumn('property_media', 'type')) {
                    $table->string('type');
                }
                if (! Schema::hasColumn('property_media', 'order')) {
                    $table->unsignedInteger('order')->default(0);
                }
                if (! Schema::hasColumn('property_media', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down(): void
    {
        // This migration only ensures schema correctness; it does not drop tables to avoid data loss.
    }
};
