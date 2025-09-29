<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidWebhookSignatureException;
use App\Models\Verification;
use App\Services\KycProvider;
use Illuminate\Http\Request;
use Throwable;

class VerificationController extends Controller
{
    public function start(KycProvider $kyc)
    {
        $tenant = tenant();

        $verification = Verification::where('tenant_id', $tenant->id)->latest()->first();

        if (! $verification || ! $verification->provider_session_url || in_array($verification->status, ['error', 'complete', 'approved', 'declined'])) {
            try {
                $verification = $kyc->start($tenant);
            } catch (Throwable $exception) {
                report($exception);

                $verification = Verification::create([
                    'tenant_id' => $tenant->id,
                    'status' => 'error',
                    'provider' => $kyc->providerName(),
                    'error_message' => 'Unable to start identity verification. Please contact support if the issue persists.',
                ]);
            }
        }

        return view('tenant.onboarding.start', compact('verification'));
    }

    public function callback(Request $request, KycProvider $kyc)
    {
        $payload = $request->json()->all();
        $rawBody = $request->getContent();
        $signature = $request->header('X-SHA2-Signature');

        try {
            $kyc->handleCallback($rawBody, $payload, $signature);
        } catch (Throwable $exception) {
            if ($exception instanceof InvalidWebhookSignatureException) {
                return response()->json(['message' => 'Invalid signature'], 400);
            }

            report($exception);

            return response()->json(['message' => 'Unable to process webhook'], 500);
        }

        return response()->json(['message' => 'accepted']);
    }

    public function status()
    {
        $verification = Verification::where('tenant_id', tenant('id'))->latest()->first();

        return view('tenant.onboarding.status', compact('verification'));
    }
}
