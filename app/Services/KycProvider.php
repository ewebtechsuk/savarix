<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Verification;

class KycProvider
{
    public function start(Tenant $tenant): string
    {
        // In a real implementation this would call an external API
        // and return a URL for the user to complete verification.
        return 'https://example-kyc.test/session/' . $tenant->id;
    }

    public function handleCallback(array $payload): void
    {
        if (! isset($payload['reference']) || ! isset($payload['status'])) {
            return;
        }

        Verification::where('provider_reference', $payload['reference'])
            ->update(['status' => $payload['status']]);
    }

    public function providerName(): string
    {
        return 'onfido';
    }
}
