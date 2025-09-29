<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Verification;
use App\Exceptions\InvalidWebhookSignatureException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use RuntimeException;

class KycProvider
{
    public function start(Tenant $tenant): Verification
    {
        $workflowId = Config::get('services.onfido.workflow_id');

        if (! $workflowId) {
            throw new RuntimeException('Onfido workflow ID is not configured.');
        }

        $workflowResponse = $this->client()->post('/v3.6/workflow_runs', [
            'workflow_id' => $workflowId,
            'applicant' => array_filter([
                'first_name' => Arr::get($tenant->data, 'contact.first_name', 'Onboarding'),
                'last_name' => Arr::get($tenant->data, 'contact.last_name', 'Tenant'),
                'email' => Arr::get($tenant->data, 'contact.email'),
            ]),
            'tags' => [
                'tenant_id:' . $tenant->id,
            ],
            'metadata' => [
                'tenant_id' => $tenant->id,
            ],
        ]);

        $workflow = $this->extractResponseData($workflowResponse, 'Failed to create verification session');

        $reference = Arr::get($workflow, 'id');

        if (! $reference) {
            throw new RuntimeException('Onfido did not return a workflow run identifier.');
        }

        $shareResponse = $this->client()->post("/v3.6/workflow_runs/{$reference}/share_link", [
            'ttl' => Config::get('services.onfido.share_link_ttl', 3600),
        ]);

        $shareLink = $this->extractResponseData($shareResponse, 'Failed to create verification share link');

        $sessionUrl = Arr::get($shareLink, 'url');

        if (! $sessionUrl) {
            throw new RuntimeException('Onfido did not return a verification session URL.');
        }

        return Verification::create([
            'tenant_id' => $tenant->id,
            'status' => Arr::get($workflow, 'status', 'started'),
            'provider' => $this->providerName(),
            'provider_reference' => $reference,
            'provider_session_url' => $sessionUrl,
            'session_metadata' => [
                'workflow_run' => $workflow,
                'share_link' => $shareLink,
            ],
        ]);
    }

    public function handleCallback(string $rawBody, array $payload, ?string $signature): void
    {
        if (! $this->isValidSignature($rawBody, $signature)) {
            throw new InvalidWebhookSignatureException('Invalid Onfido webhook signature.');
        }

        $reference = Arr::get($payload, 'payload.object.id')
            ?? Arr::get($payload, 'object.id')
            ?? Arr::get($payload, 'id')
            ?? Arr::get($payload, 'reference');

        if (! $reference) {
            Log::warning('Onfido webhook received without reference.', ['payload' => $payload]);

            return;
        }

        $status = Arr::get($payload, 'payload.object.status')
            ?? Arr::get($payload, 'status');

        $errorCode = Arr::get($payload, 'payload.object.error.code')
            ?? Arr::get($payload, 'error.code');

        $errorMessage = Arr::get($payload, 'payload.object.error.message')
            ?? Arr::get($payload, 'error.message');

        $verification = Verification::where('provider_reference', $reference)->first();

        if (! $verification) {
            Log::warning('Onfido webhook received for unknown verification.', [
                'reference' => $reference,
                'payload' => $payload,
            ]);

            return;
        }

        $metadata = $verification->session_metadata ?? [];
        $metadata['last_webhook'] = $payload;

        $verification->fill([
            'status' => $status ?? $verification->status,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'session_metadata' => $metadata,
        ]);

        $verification->save();
    }

    public function providerName(): string
    {
        return 'onfido';
    }

    protected function client(): PendingRequest
    {
        $token = Config::get('services.onfido.api_token');

        if (! $token) {
            throw new RuntimeException('Onfido API token is not configured.');
        }

        $baseUrl = rtrim(Config::get('services.onfido.base_url', 'https://api.eu.onfido.com'), '/');
        $version = Config::get('services.onfido.version', 'v3.6');

        return Http::baseUrl($baseUrl)
            ->withHeaders([
                'Authorization' => 'Token token=' . $token,
                'Onfido-Version' => $version,
                'Accept' => 'application/json',
            ]);
    }

    protected function extractResponseData(Response $response, string $errorPrefix): array
    {
        if ($response->failed()) {
            throw new RuntimeException(sprintf('%s: %s', $errorPrefix, $response->body()));
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException(sprintf('%s: Unexpected response.', $errorPrefix));
        }

        return $data;
    }

    protected function isValidSignature(string $rawBody, ?string $signature): bool
    {
        $secret = Config::get('services.onfido.webhook_secret');

        if (! $secret || ! $signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $signature);
    }
}
