<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use App\Services\KycProvider;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function start(KycProvider $kyc)
    {
        $tenant = tenant();

        $reference = $kyc->start($tenant);

        Verification::create([
            'tenant_id' => $tenant->id,
            'status' => 'started',
            'provider' => $kyc->providerName(),
            'provider_reference' => $reference,
        ]);

        return view('tenant.onboarding.start', [
            'verificationUrl' => $reference,
        ]);
    }

    public function callback(Request $request, KycProvider $kyc)
    {
        $kyc->handleCallback($request->all());

        return redirect()->route('verification.status');
    }

    public function status()
    {
        $verification = Verification::where('tenant_id', tenant('id'))->latest()->first();

        return view('tenant.onboarding.status', compact('verification'));
    }
}
