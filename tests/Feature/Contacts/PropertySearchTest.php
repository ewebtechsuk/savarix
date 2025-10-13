<?php

namespace Tests\Feature\Contacts;

use App\Http\Controllers\ContactController;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PropertySearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_properties_excludes_assigned_by_default(): void
    {
        $tenant = Tenant::factory()->create(['id' => 'tenant-alpha', 'slug' => 'tenant-alpha']);

        $available = Property::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'landlord_id' => null,
            'title' => 'Alpha House',
            'address' => '1 Alpha Street',
        ]);

        Property::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'landlord_id' => 73,
            'title' => 'Alpha Court',
            'address' => '99 Court Road',
        ]);

        tenancy()->initialize($tenant);

        $response = app(ContactController::class)->searchProperties(
            Request::create('/contacts/properties/search', 'GET', ['q' => 'Alpha'])
        );

        tenancy()->end();

        $data = $response->getData(true);

        $this->assertCount(1, $data);
        $this->assertSame($available->id, $data[0]['id']);
        $this->assertStringContainsString($available->title, $data[0]['text']);
        $this->assertStringContainsString($available->address, $data[0]['text']);
    }

    public function test_search_properties_can_include_assigned_when_requested(): void
    {
        $tenant = Tenant::factory()->create(['id' => 'tenant-bravo', 'slug' => 'tenant-bravo']);

        $assigned = Property::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'landlord_id' => 42,
            'title' => 'Bravo Lodge',
            'address' => '45 Bravo Way',
        ]);

        tenancy()->initialize($tenant);

        $response = app(ContactController::class)->searchProperties(
            Request::create('/contacts/properties/search', 'GET', [
                'q' => 'Bravo',
                'unassigned' => '0',
            ])
        );

        tenancy()->end();

        $data = $response->getData(true);

        $this->assertNotEmpty($data);
        $this->assertSame($assigned->id, $data[0]['id']);
    }

    public function test_search_properties_ignores_other_tenants_inventory(): void
    {
        $primaryTenant = Tenant::factory()->create(['id' => 'tenant-delta', 'slug' => 'tenant-delta']);
        $otherTenant = Tenant::factory()->create(['id' => 'tenant-epsilon', 'slug' => 'tenant-epsilon']);

        $visible = Property::factory()->create([
            'tenant_id' => $primaryTenant->getKey(),
            'landlord_id' => null,
            'title' => 'Delta Place',
            'address' => '123 Delta Road',
        ]);

        Property::factory()->create([
            'tenant_id' => $otherTenant->getKey(),
            'landlord_id' => null,
            'title' => 'Delta Villas',
            'address' => '888 Elsewhere Lane',
        ]);

        tenancy()->initialize($primaryTenant);

        $response = app(ContactController::class)->searchProperties(
            Request::create('/contacts/properties/search', 'GET', [
                'q' => 'Delta',
                'unassigned' => '0',
            ])
        );

        tenancy()->end();

        $data = $response->getData(true);

        $this->assertCount(1, $data);
        $this->assertSame($visible->id, $data[0]['id']);
    }
}

