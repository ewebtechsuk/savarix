<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PartnerIntegrationApiTest extends TestCase
{
    public function test_authenticated_users_can_manage_integrations(): void
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $createResponse = $this->postJson('/api/integrations', [
            'name' => 'Zoopla Connect',
            'provider' => 'Zoopla',
            'type' => 'portal',
            'credentials' => ['api_key' => 'key'],
            'settings' => ['webhook_url' => 'https://zoopla.example/webhook'],
        ]);

        $createResponse->assertCreated();

        $integrationId = $createResponse->json('id');

        $this->assertDatabaseHas('partner_integrations', [
            'id' => $integrationId,
            'name' => 'Zoopla Connect',
        ]);

        $updateResponse = $this->putJson("/api/integrations/{$integrationId}", [
            'name' => 'Zoopla Connect',
            'provider' => 'Zoopla',
            'type' => 'portal',
            'credentials' => ['api_key' => 'updated'],
            'settings' => ['webhook_url' => 'https://zoopla.example/webhook', 'headers' => ['X-Test' => '1']],
            'active' => false,
        ]);

        $updateResponse->assertOk();

        $this->assertDatabaseHas('partner_integrations', [
            'id' => $integrationId,
            'active' => false,
        ]);

        $indexResponse = $this->getJson('/api/integrations');
        $indexResponse->assertOk()->assertJsonCount(1);

        $deleteResponse = $this->deleteJson("/api/integrations/{$integrationId}");
        $deleteResponse->assertNoContent();

        $this->assertDatabaseMissing('partner_integrations', [
            'id' => $integrationId,
        ]);
    }
}

