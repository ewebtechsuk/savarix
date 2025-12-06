<?php

namespace Tests\Feature\Tenancy;

use App\Models\Contact;
use App\Services\TenantProvisioner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContactNotesColumnTest extends TestCase
{
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

        $useTenantDatabase = function ($tenant): string {
            $tenantDatabase = database_path($tenant->database()->getName());
            config(['database.connections.sqlite.database' => $tenantDatabase]);
            DB::purge('sqlite');

            return $tenantDatabase;
        };

        $resetDatabase = function () use ($originalDatabase): void {
            config(['database.connections.sqlite.database' => $originalDatabase]);
            DB::purge('sqlite');
        };

        tenancy()->initialize($tenant);

        try {
            $useTenantDatabase($tenant);

            if (! Schema::hasTable('contacts')) {
                $migration = require base_path('database/migrations/tenant/2026_09_30_000003_ensure_contact_and_property_media_columns.php');
                $migration->up();
            }

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
            $resetDatabase();
            tenancy()->end();
        }
    }
}
