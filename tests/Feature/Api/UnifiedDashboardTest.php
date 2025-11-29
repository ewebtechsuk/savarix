<?php

namespace Tests\Feature\Api;

use App\Models\Contact;
use App\Models\Invoice;
use App\Models\PartnerIntegration;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyPortals;
use App\Models\SavarixTenancy;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UnifiedDashboardTest extends TestCase
{
    public function test_it_returns_unified_metrics_snapshot(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $vendor = Contact::factory()->create();

        $property = Property::factory()->create([
            'type' => 'sales',
            'status' => 'available',
            'price' => 275000,
            'vendor_id' => $vendor->id,
            'publish_to_portal' => true,
        ]);

        Property::factory()->create([
            'type' => 'lettings',
            'status' => 'let_agreed',
            'price' => 1250,
            'vendor_id' => $vendor->id,
            'publish_to_portal' => false,
        ]);

        PropertyPortals::create([
            'property' => $property->id,
            'rightmove' => true,
            'zoopla' => true,
        ]);

        $tenantContact = Contact::factory()->create();

        $tenancy = SavarixTenancy::create([
            'property_id' => $property->id,
            'contact_id' => $tenantContact->id,
            'start_date' => Carbon::now()->subMonths(2),
            'end_date' => Carbon::now()->addWeeks(2),
            'rent' => 950,
            'status' => 'active',
        ]);

        Invoice::create([
            'number' => 'INV-100',
            'date' => Carbon::now()->subWeek(),
            'contact_id' => $tenantContact->id,
            'property_id' => $property->id,
            'tenancy_id' => $tenancy->id,
            'amount' => 500,
            'status' => 'sent',
            'due_date' => Carbon::now()->addDays(3),
        ]);

        Invoice::create([
            'number' => 'INV-101',
            'date' => Carbon::now()->subWeeks(2),
            'contact_id' => $tenantContact->id,
            'property_id' => $property->id,
            'tenancy_id' => $tenancy->id,
            'amount' => 200,
            'status' => 'paid',
            'due_date' => Carbon::now()->subWeek(),
        ]);

        Payment::create([
            'tenancy_id' => $tenancy->id,
            'amount' => 200,
            'status' => 'completed',
        ]);

        PartnerIntegration::create([
            'name' => 'Rightmove Real Time',
            'provider' => 'Rightmove',
            'type' => 'portal',
            'credentials' => ['branch' => 'RM100'],
            'settings' => ['webhook_url' => 'https://example.com/hook'],
            'active' => true,
        ]);

        $response = $this->getJson('/api/dashboard/unified');

        $response->assertOk();

        $response->assertJsonPath('sales.total', 1);
        $response->assertJsonPath('sales.status_breakdown.available', 1);
        $response->assertJsonPath('lettings.active', 1);
        $response->assertJsonPath('accounting.open_invoices', 1);
        $response->assertJsonPath('portal_publications.total_published', 1);
        $response->assertJsonPath('portal_publications.distribution.rightmove', 1);
        $response->assertJsonPath('active_integrations.0.name', 'Rightmove Real Time');
    }
}

