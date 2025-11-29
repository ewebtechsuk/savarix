<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\ContactCommunication;
use App\Models\Landlord;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\SavarixTenancy;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LandlordDashboardTest extends TestCase
{
    public function test_authenticated_landlord_sees_portfolio_metrics(): void
    {
        $landlord = Landlord::factory()->create([
            'contact_email' => 'owner@example.com',
            'person_firstname' => 'Olivia',
            'person_lastname' => 'Owner',
        ]);

        $landlordContact = Contact::create([
            'type' => 'landlord',
            'name' => 'Olivia Owner',
            'email' => 'owner@example.com',
        ]);

        $tenantContact = Contact::create([
            'type' => 'tenant',
            'name' => 'Alex Renter',
            'email' => 'alex@example.com',
        ]);

        $tenantEntity = Tenant::factory()->create([
            'id' => 'tenant-for-landlord',
            'name' => 'Tenant Example',
        ]);

        $property = Property::create([
            'title' => '12 Market Street',
            'status' => 'occupied',
            'landlord_id' => $landlordContact->id,
            'tenant_id' => $tenantEntity->getKey(),
            'price' => 1500,
        ]);

        $tenancy = SavarixTenancy::create([
            'property_id' => $property->id,
            'contact_id' => $tenantContact->id,
            'start_date' => now()->subMonths(2),
            'end_date' => null,
            'rent' => 1500,
            'status' => 'active',
        ]);

        Payment::create([
            'tenancy_id' => $tenancy->id,
            'amount' => 1500,
            'status' => 'pending',
        ]);

        MaintenanceRequest::create([
            'property_id' => $property->id,
            'tenant_id' => $tenantEntity->getKey(),
            'description' => 'Heating system requires urgent attention',
            'status' => 'in_progress',
        ]);

        ContactCommunication::create([
            'contact_id' => $landlordContact->id,
            'user_id' => null,
            'communication' => 'Tenant requested a boiler service appointment.',
        ]);

        // Seed a second landlord portfolio that should remain hidden from the primary landlord.
        $otherLandlord = Landlord::factory()->create([
            'contact_email' => 'someoneelse@example.com',
            'person_firstname' => 'Sam',
            'person_lastname' => 'Someone',
        ]);

        $otherLandlordContact = Contact::create([
            'type' => 'landlord',
            'name' => 'Sam Someone',
            'email' => 'someoneelse@example.com',
        ]);

        $otherProperty = Property::create([
            'title' => 'Hidden Villa',
            'status' => 'occupied',
            'landlord_id' => $otherLandlordContact->id,
            'price' => 950,
        ]);

        $unrelatedTenancy = SavarixTenancy::create([
            'property_id' => $otherProperty->id,
            'contact_id' => $tenantContact->id,
            'start_date' => now()->subMonths(1),
            'rent' => 950,
            'status' => 'active',
        ]);

        Payment::create([
            'tenancy_id' => $unrelatedTenancy->id,
            'amount' => 950,
            'status' => 'pending',
        ]);

        Auth::guard('landlord')->login($landlord);

        $response = $this->get(route('landlord.dashboard'));

        $response->assertOk();
        $response->assertSee('12 Market Street');
        $response->assertSee('£1,500.00');
        $response->assertSee('Heating system requires urgent attention');
        $response->assertSee('Tenant requested a boiler service appointment.');
        $response->assertDontSee('Hidden Villa');
        $response->assertDontSee('£950.00');
    }
}
