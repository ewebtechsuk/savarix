<?php

namespace Tests\Feature\Tenancy;

use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Property;
use App\Models\SavarixTenancy;
use App\Services\TenantProvisioner;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\Concerns\UsesTenantSqliteDatabase;
use Tests\TestCase;

class TenantSchemaIsolationTest extends TestCase
{
    use UsesTenantSqliteDatabase;

    public function test_tenant_core_tables_are_isolated_and_present(): void
    {
        $provisioner = app(TenantProvisioner::class);

        config(['database.connections.tenant' => config('database.connections.sqlite')]);

        $firstTenant = $provisioner
            ->provision([
                'subdomain' => 'tenancy-a-' . Str::random(6),
                'name' => 'Tenancy A',
            ])
            ->tenant();

        $secondTenant = $provisioner
            ->provision([
                'subdomain' => 'tenancy-b-' . Str::random(6),
                'name' => 'Tenancy B',
            ])
            ->tenant();

        $this->assertNotNull($firstTenant, 'First tenant provisioning failed.');
        $this->assertNotNull($secondTenant, 'Second tenant provisioning failed.');

        $originalDatabase = config('database.connections.sqlite.database');

        $firstInvoiceNumber = null;

        tenancy()->initialize($firstTenant);

        try {
            $this->useTenantDatabase($firstTenant);
            $this->ensureTenantSchema();

            $this->assertTrue(Schema::hasTable('tenancies'));
            $this->assertTrue(Schema::hasTable('invoices'));
            $this->assertTrue(Schema::hasTable('payments'));

            $firstContact = Contact::factory()->create([
                'name' => 'Contact for first tenancy',
                'type' => 'tenant',
            ]);
            $firstProperty = Property::factory()->create([
                'title' => 'Property for first tenancy',
            ]);

            $firstTenancy = SavarixTenancy::create([
                'contact_id' => $firstContact->id,
                'property_id' => $firstProperty->id,
                'start_date' => now()->toDateString(),
                'rent' => 1000,
                'status' => 'active',
                'notes' => 'First tenancy notes',
            ]);

            $firstInvoiceNumber = 'INV-' . Str::upper(Str::random(6));

            Invoice::create([
                'number' => $firstInvoiceNumber,
                'date' => now()->toDateString(),
                'contact_id' => $firstContact->id,
                'property_id' => $firstProperty->id,
                'tenancy_id' => $firstTenancy->id,
                'amount' => 500,
                'status' => 'unpaid',
                'due_date' => now()->addWeek()->toDateString(),
                'notes' => 'First invoice notes',
            ]);
        } finally {
            $this->resetTenantDatabase($originalDatabase);
            tenancy()->end();
        }

        tenancy()->initialize($secondTenant);

        try {
            $this->useTenantDatabase($secondTenant);
            $this->ensureTenantSchema();

            $secondContact = Contact::factory()->create([
                'name' => 'Contact for second tenancy',
                'type' => 'tenant',
            ]);
            $secondProperty = Property::factory()->create([
                'title' => 'Property for second tenancy',
            ]);

            $secondTenancy = SavarixTenancy::create([
                'contact_id' => $secondContact->id,
                'property_id' => $secondProperty->id,
                'start_date' => now()->toDateString(),
                'rent' => 2000,
                'status' => 'pending',
                'notes' => 'Second tenancy notes',
            ]);

            $secondInvoiceNumber = 'INV-' . Str::upper(Str::random(6));

            Invoice::create([
                'number' => $secondInvoiceNumber,
                'date' => now()->toDateString(),
                'contact_id' => $secondContact->id,
                'property_id' => $secondProperty->id,
                'tenancy_id' => $secondTenancy->id,
                'amount' => 750,
                'status' => 'unpaid',
                'due_date' => now()->addWeek()->toDateString(),
                'notes' => 'Second invoice notes',
            ]);

            $this->assertSame(['pending'], SavarixTenancy::pluck('status')->all());
            $this->assertSame([$secondInvoiceNumber], Invoice::pluck('number')->all());
        } finally {
            $this->resetTenantDatabase($originalDatabase);
            tenancy()->end();
        }

        tenancy()->initialize($firstTenant);

        try {
            $this->useTenantDatabase($firstTenant);

            $this->assertSame(['active'], SavarixTenancy::pluck('status')->all());
            $this->assertSame([$firstInvoiceNumber], Invoice::pluck('number')->all());
        } finally {
            $this->resetTenantDatabase($originalDatabase);
            tenancy()->end();
        }
    }
}
