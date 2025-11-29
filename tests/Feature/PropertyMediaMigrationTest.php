<?php

namespace Tests\Feature;

use App\Models\PropertyMedia;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PropertyMediaMigrationTest extends TestCase
{
    public function test_fresh_migrations_include_is_featured_column(): void
    {
        $this->assertTrue(Schema::hasColumn('property_media', 'is_featured'));

        $media = new PropertyMedia(['is_featured' => 1]);

        $this->assertTrue($media->is_featured);
    }

    public function test_feature_flag_migration_runs_without_order_column(): void
    {
        Schema::dropIfExists('property_media');

        Schema::create('property_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            $table->string('file_path');
            $table->string('type');
            $table->timestamps();
        });

        $this->assertFalse(Schema::hasColumn('property_media', 'order'));
        $this->assertFalse(Schema::hasColumn('property_media', 'is_featured'));

        $migration = require base_path('database/migrations/2025_08_16_000002_add_featured_flag_to_property_media.php');
        $migration->up();

        $this->assertTrue(Schema::hasColumn('property_media', 'is_featured'));

        DB::table('property_media')->insert([
            'property_id' => 1,
            'file_path' => 'path.jpg',
            'type' => 'image',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $record = DB::table('property_media')->first();

        $this->assertSame(0, (int) $record->is_featured);
    }
}
