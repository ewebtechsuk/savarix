<?php

namespace Tests\Feature\Tenancy;

use App\Models\Contact;
use App\Services\TenantProvisioner;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\UsesTenantSqliteDatabase;
use Tests\TestCase;

class ContactNotesColumnTest extends TestCase
{
    use UsesTenantSqliteDatabase;

    public function test_contacts_table_accepts_notes_for_tenant(): void
    {
        $provisioner = app(TenantProvisioner::class);

        config(['database.connections.tenant' => config('database.connections.sqlite')]);

        $tenant = $provisioner
            ->provision([
                'subdomain' => 'aktonz-notes-' . Str::random(6),
                'name' => 'Aktonz Notes Tenant',
            ])
            ->tenant();

        $this->assertNotNull($tenant, 'Tenant provisioning failed.');

        $originalDatabase = config('database.connections.sqlite.database');

        tenancy()->initialize($tenant);

        try {
            $this->useTenantDatabase($tenant);
            $this->ensureTenantSchema();

            $this->assertTrue(Schema::hasTable('contacts'));
            $this->assertTrue(Schema::hasColumn('contacts', 'notes'));

            $contact = Contact::factory()->create([
                'type' => 'tenant',
                'name' => 'Test Contact',
                'email' => 'contact@example.com',
                'notes' => 'Added via tenant migration test.',
            ]);

            $this->assertNotNull($contact->id);
            $this->assertSame('Added via tenant migration test.', Contact::first()->notes);
        } finally {
            $this->resetTenantDatabase($originalDatabase);
            tenancy()->end();
        }
    }
}
