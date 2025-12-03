<?php

namespace Tests\Feature\Tenancy;

use App\Models\Contact;
use App\Services\TenantProvisioner;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContactNotesColumnTest extends TestCase
{
    public function test_contacts_table_accepts_notes_for_tenant(): void
    {
        $tenant = app(TenantProvisioner::class)
            ->provision([
                'subdomain' => 'aktonz-notes-' . Str::random(6),
                'name' => 'Aktonz Notes Tenant',
            ])
            ->tenant();

        $this->assertNotNull($tenant, 'Tenant provisioning failed.');

        tenancy()->initialize($tenant);

        try {
            Schema::dropIfExists('contacts');

            Schema::create('contacts', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->string('type');
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
            });

            $this->assertFalse(Schema::hasColumn('contacts', 'notes'));

            $migration = require base_path('database/migrations/tenant/2026_02_22_000002_add_notes_to_contacts_table.php');
            $migration->up();

            $this->assertTrue(Schema::hasColumn('contacts', 'notes'));

            $contact = Contact::create([
                'type' => 'tenant',
                'name' => 'Test Contact',
                'email' => 'contact@example.com',
                'phone' => '0123456789',
                'address' => '123 Example Street',
                'notes' => 'Added via tenant migration test.',
            ]);

            $this->assertNotNull($contact->id);
            $this->assertSame('Added via tenant migration test.', $contact->fresh()->notes);
        } finally {
            tenancy()->end();
        }
    }
}
