<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\ContactCommunication;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class TenantDashboardTest extends TestCase
{
    public function test_authenticated_tenant_sees_dynamic_dashboard_metrics(): void
    {
        $tenant = Tenant::factory()->create([
            'id' => 'tenant-test',
            'name' => 'Test Tenant',
        ]);

        $contact = Contact::create([
            'type' => 'tenant',
            'name' => 'Jamie Tenant',
            'email' => 'jamie@example.com',
        ]);

        $property = Property::create([
            'title' => 'Flat 2B, High Street',
            'status' => 'occupied',
            'tenant_id' => $tenant->getKey(),
            'price' => 1200,
        ]);

        $tenancy = Tenancy::create([
            'property_id' => $property->id,
            'contact_id' => $contact->id,
            'start_date' => now()->subMonths(3),
            'end_date' => null,
            'rent' => 1200,
            'status' => 'active',
        ]);

        Payment::create([
            'tenancy_id' => $tenancy->id,
            'amount' => 1200,
            'status' => 'pending',
        ]);

        MaintenanceRequest::create([
            'property_id' => $property->id,
            'tenant_id' => $tenant->getKey(),
            'description' => 'Leaky kitchen tap needing repair',
            'status' => 'pending',
        ]);

        ContactCommunication::create([
            'contact_id' => $contact->id,
            'user_id' => null,
            'communication' => 'Reminder: maintenance visit scheduled for Friday.',
        ]);

        // Data belonging to another tenant should not bleed into the dashboard.
        $otherTenant = Tenant::factory()->create([
            'id' => 'tenant-other',
            'name' => 'Someone Else',
        ]);

        $otherContact = Contact::create([
            'type' => 'tenant',
            'name' => 'Unaffiliated Tenant',
            'email' => 'other@example.com',
        ]);

        $otherProperty = Property::create([
            'title' => 'Different Property',
            'status' => 'occupied',
            'tenant_id' => $otherTenant->getKey(),
            'price' => 1100,
        ]);

        $otherTenancy = Tenancy::create([
            'property_id' => $otherProperty->id,
            'contact_id' => $otherContact->id,
            'start_date' => now()->subMonths(5),
            'rent' => 1100,
            'status' => 'active',
        ]);

        Payment::create([
            'tenancy_id' => $otherTenancy->id,
            'amount' => 1100,
            'status' => 'pending',
        ]);

        Auth::guard('tenant')->loginUsingId($tenant->getKey());

        $response = $this->get(route('tenant.dashboard'));

        $response->assertOk();
        $response->assertSee('Flat 2B, High Street');
        $response->assertSee('£1,200.00');
        $response->assertSee('Leaky kitchen tap needing repair');
        $response->assertSee('Reminder: maintenance visit scheduled for Friday.');
        $response->assertDontSee('Different Property');
        $response->assertDontSee('£1,100.00');
    }
}
