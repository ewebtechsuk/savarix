<?php

namespace Tests\Feature\Tenancy;

use App\Services\TenantProvisioner;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class PropertyMediaOrderColumnTest extends TestCase
{
    public function test_property_media_order_column_exists_for_tenant(): void
    {
        $tenant = app(TenantProvisioner::class)
            ->provision([
                'subdomain' => 'aktonz-order-' . Str::random(6),
                'name' => 'Aktonz Order Tenant',
            ])
            ->tenant();

        $this->assertNotNull($tenant, 'Tenant provisioning failed.');

        tenancy()->initialize($tenant);

        try {
            Schema::dropIfExists('property_media');

            Schema::create('property_media', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('property_id');
                $table->string('file_path');
                $table->string('type');
                $table->timestamps();
            });

            $this->assertFalse(Schema::hasColumn('property_media', 'order'));

            $migration = require base_path('database/migrations/tenant/2026_02_22_000000_add_order_to_property_media_table.php');
            $migration->up();

            $this->assertTrue(Schema::hasColumn('property_media', 'order'));

            DB::table('property_media')->insert([
                'property_id' => 1,
                'file_path' => 'path.jpg',
                'type' => 'image',
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $records = DB::table('property_media')->orderBy('order')->get();

            $this->assertCount(1, $records);
            $this->assertSame(2, (int) $records->first()->order);
        } finally {
            tenancy()->end();
        }
    }
}
