<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Verification;
use App\Services\ConversionTrackingService;
use App\Services\KycProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Tenant;
use Throwable;

class OnboardingController extends Controller
{
    public function showRegistrationForm(Request $request, ConversionTrackingService $tracking)
    {
        $tracking->record(
            'marketing.signup_view',
            [
                'referer' => $request->headers->get('referer'),
                'source' => $request->query('source'),
            ],
            $request->session()->getId()
        );

        return view('onboarding.register');
    }

    public function register(
        Request $request,
        ConversionTrackingService $tracking,
        KycProvider $kyc
    ) {
        $data = $request->validate([
            'company' => 'required|string|unique:tenants,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
            'tracking_session' => 'nullable|string|max:64',
            'source' => 'nullable|string|max:120',
        ]);

        $sessionId = $data['tracking_session'] ?? $request->session()->getId();

        $tracking->record(
            'marketing.signup_submitted',
            [
                'company' => $data['company'],
                'email' => $data['email'],
                'source' => $data['source'] ?? null,
            ],
            $sessionId
        );

        $tenantId = Str::of($data['company'])->replaceMatches('/[^a-zA-Z0-9]+/', '')->lower();
        $domainHost = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';
        $domain = $tenantId . '.' . $domainHost;

        $tenant = null;
        $verification = null;
        $verificationUrl = null;
        $verificationError = null;

        DB::beginTransaction();

        try {
            $tenant = Tenant::create(['id' => (string) $tenantId]);
            $tenant->domains()->create(['domain' => $domain]);
            $tenant->createDatabase();

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            $tracking->record(
                'marketing.signup_failed',
                ['reason' => $exception->getMessage(), 'company' => $data['company']],
                $sessionId
            );
            throw $exception;
        }

        tenancy()->initialize($tenant);

        try {
            Artisan::call('tenants:migrate', ['--tenants' => [$tenant->id]]);

            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
        } finally {
            tenancy()->end();
        }

        try {
            $verification = $kyc->start($tenant);
            $verificationUrl = $verification->provider_session_url;
        } catch (Throwable $exception) {
            report($exception);

            $verification = Verification::create([
                'tenant_id' => $tenant->id,
                'status' => 'error',
                'provider' => $kyc->providerName(),
                'provider_reference' => null,
                'error_message' => 'Unable to start identity verification. Our team has been notified.',
            ]);

            $verificationError = 'We were unable to start the identity verification session. Please try again later or contact support.';
        }

        $tracking->record(
            'marketing.signup_completed',
            [
                'tenant_id' => $tenant->id,
                'domain' => $domain,
                'verification_reference' => $verification?->provider_reference,
                'verification_status' => $verification?->status,
            ],
            $sessionId
        );

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'tenant_id' => $tenant->id,
                'domain' => $domain,
                'verification_reference' => $verification?->provider_reference,
                'verification_url' => $verificationUrl,
                'verification_error' => $verificationError,
            ], 201);
        }

        return redirect('https://' . $domain . '/login')
            ->with('success', 'Account created! Please log in.')
            ->with('verification_error', $verificationError)
            ->with('verification_status', $verification?->status);
    }
}
