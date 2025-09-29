<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Verification;
use App\Services\KycProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KycVerificationTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', database_path('database.sqlite'));
        Config::set('services.onfido.api_token', 'token_test');
        Config::set('services.onfido.workflow_id', 'wf-123');
        Config::set('services.onfido.webhook_secret', 'secret-key');
        Config::set('services.onfido.base_url', 'https://api.onfido.test');

        Artisan::call('migrate:fresh', ['--database' => 'sqlite', '--force' => true]);
    }

    public function test_it_creates_and_persists_verification_session(): void
    {
        Http::fake([
            'api.onfido.test/v3.6/workflow_runs' => Http::response([
                'id' => 'run-123',
                'status' => 'pending',
            ], 201),
            'api.onfido.test/v3.6/workflow_runs/run-123/share_link' => Http::response([
                'url' => 'https://share.onfido.test/link/run-123',
                'expires_at' => now()->addHour()->toIso8601String(),
            ], 201),
        ]);

        $tenant = Tenant::factory()->create([
            'data' => [
                'contact' => [
                    'first_name' => 'Test',
                    'last_name' => 'Tenant',
                    'email' => 'test@example.com',
                ],
            ],
        ]);

        $provider = app(KycProvider::class);

        $verification = $provider->start($tenant);

        $this->assertInstanceOf(Verification::class, $verification);
        $this->assertSame('run-123', $verification->provider_reference);
        $this->assertSame('https://share.onfido.test/link/run-123', $verification->provider_session_url);
        $this->assertDatabaseHas('verifications', [
            'tenant_id' => $tenant->id,
            'provider_reference' => 'run-123',
            'provider_session_url' => 'https://share.onfido.test/link/run-123',
        ]);
        $this->assertArrayHasKey('workflow_run', $verification->session_metadata);
    }

    public function test_it_throws_when_session_creation_fails(): void
    {
        Http::fake([
            'api.onfido.test/v3.6/workflow_runs' => Http::response([
                'error' => ['message' => 'Bad request'],
            ], 422),
        ]);

        $tenant = Tenant::factory()->create();

        $provider = app(KycProvider::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to create verification session');

        $provider->start($tenant);
    }

    public function test_webhook_updates_verification_on_valid_signature(): void
    {
        Http::fake([
            'api.onfido.test/v3.6/workflow_runs' => Http::response([
                'id' => 'run-123',
                'status' => 'pending',
            ], 201),
            'api.onfido.test/v3.6/workflow_runs/run-123/share_link' => Http::response([
                'url' => 'https://share.onfido.test/link/run-123',
            ], 201),
        ]);

        $tenant = Tenant::factory()->create();

        $verification = app(KycProvider::class)->start($tenant);

        $payload = [
            'payload' => [
                'object' => [
                    'id' => 'run-123',
                    'status' => 'approved',
                ],
            ],
        ];

        $raw = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $raw, Config::get('services.onfido.webhook_secret'));

        $response = $this->withHeaders(['X-SHA2-Signature' => $signature])
            ->postJson('/webhooks/onfido', $payload);

        $response->assertOk();

        $this->assertDatabaseHas('verifications', [
            'id' => $verification->id,
            'status' => 'approved',
        ]);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        Http::fake([
            'api.onfido.test/v3.6/workflow_runs' => Http::response([
                'id' => 'run-123',
                'status' => 'pending',
            ], 201),
            'api.onfido.test/v3.6/workflow_runs/run-123/share_link' => Http::response([
                'url' => 'https://share.onfido.test/link/run-123',
            ], 201),
        ]);

        $tenant = Tenant::factory()->create();

        app(KycProvider::class)->start($tenant);

        $payload = [
            'payload' => [
                'object' => [
                    'id' => 'run-123',
                    'status' => 'approved',
                ],
            ],
        ];

        $response = $this->withHeaders(['X-SHA2-Signature' => 'bad-signature'])
            ->postJson('/webhooks/onfido', $payload);

        $response->assertStatus(400);

        $this->assertDatabaseHas('verifications', [
            'provider_reference' => 'run-123',
            'status' => 'pending',
        ]);
    }
}
