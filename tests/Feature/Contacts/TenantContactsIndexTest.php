<?php

namespace Tests\Feature\Contacts;

use App\Models\Contact;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantContactsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_contacts_index_loads_on_tenant_domain(): void
    {
        $tenant = Tenant::factory()->create([
            'id' => 'aktonz',
            'slug' => 'aktonz',
            'data' => ['company_id' => '468173'],
        ]);

        $tenant->domains()->create(['domain' => 'aktonz.savarix.com']);

        tenancy()->initialize($tenant);

        $role = Role::findOrCreate('Admin', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        Contact::factory()->create([
            'type' => 'landlord',
            'company' => 'Aktonz Estates',
            'company_id' => '468173',
        ]);

        $this->useTenantDomain('aktonz.savarix.com');

        $response = $this->actingAs($user)->get('/contacts');

        $response->assertOk();
        $response->assertSee('Aktonz Estates');
    }
}
