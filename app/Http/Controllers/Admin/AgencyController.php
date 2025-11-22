<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Throwable;

class AgencyController extends Controller
{
    public function index(): View
    {
        return view('admin.agencies.index', [
            'agencies' => Agency::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'domain' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,suspended,trial'],
        ]);

        Agency::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'domain' => $data['domain'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        return back();
    }

    public function show(Agency $agency): View
    {
        return view('admin.agencies.show', ['agency' => $agency]);
    }

    public function update(Request $request, Agency $agency): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'domain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('agencies', 'domain')->ignore($agency->id),
            ],
        ]);

        if (! empty($validated['domain'])) {
            try {
                $validated['domain'] = Agency::normalizeDomain($validated['domain']);
            } catch (Throwable $exception) {
                Log::warning('Failed to normalize agency domain', [
                    'agency_id' => $agency->id,
                    'raw_domain' => $validated['domain'],
                    'message' => $exception->getMessage(),
                ]);

                return back()
                    ->withInput()
                    ->withErrors(['domain' => 'Unable to normalize that domain. Please check the format (e.g. aktonz.savarix.com).']);
            }
        }

        try {
            $agency->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'domain' => $validated['domain'] ?? null,
            ]);

            $agency->save();
        } catch (Throwable $exception) {
            Log::error('Failed to update agency', [
                'agency_id' => $agency->id,
                'data' => $validated,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Unable to save changes right now. Please try again or contact support.']);
        }

        return redirect()
            ->route('admin.agencies.show', $agency)
            ->with('status', 'Agency details updated.');
    }

    public function destroy(Agency $agency): RedirectResponse
    {
        $agency->delete();

        return back();
    }

    public function openTenant(Agency $agency): RedirectResponse
    {
        try {
            $dashboardUrl = $agency->tenantDashboardUrl();

            if (! $dashboardUrl) {
                Log::warning('Tenant open attempt without domain', ['agency_id' => $agency->id]);

                return back()->with('error', 'Set a domain (e.g. aktonz.savarix.com) to open the tenant app.');
            }

            Log::info('Redirecting to tenant dashboard', [
                'agency_id' => $agency->id,
                'dashboard_url' => $dashboardUrl,
            ]);

            return redirect()->away($dashboardUrl);
        } catch (\Throwable $exception) {
            Log::error('Failed to redirect to tenant dashboard', [
                'agency_id' => $agency->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Unable to build tenant dashboard URL.');
        }
    }

    public function impersonate(Request $request, Agency $agency): RedirectResponse
    {
        // Find the agency admin to impersonate
        try {
            $agencyAdmin = $agency->users()
                ->where('role', 'agency_admin')
                ->orderBy('id')
                ->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            Log::warning('Agency admin not found for impersonation', [
                'agency_id' => $agency->id,
            ]);

            return back()->with('error', 'No agency admin user exists for this agency.');
        }

        $ownerId = Auth::id();
        $session = $request->session();

        // Mark the session as impersonating and remember who started it
        $session->put([
            'impersonating' => true,
            'impersonator_id' => $ownerId,
            'impersonated_agency_id' => $agency->id,
            'impersonated_user_id' => $agencyAdmin->id,
        ]);

        // Log in as the agency admin on the central web guard
        Auth::shouldUse('web');
        Auth::guard('web')->login($agencyAdmin);

        // Regenerate the session ID so the impersonation token is bound to a fresh cookie
        $session->regenerate();

        // Build the tenant dashboard URL from the agency domain
        $dashboardUrl = $agency->tenantDashboardUrl();

        if (! $dashboardUrl) {
            Log::warning('Impersonation redirect missing domain', [
                'agency_id' => $agency->id,
            ]);

            return back()->with('error', 'Agency does not have a valid tenant domain configured.');
        }

        Log::info('Impersonation login redirecting to tenant', [
            'agency_id' => $agency->id,
            'impersonated_user_id' => $agencyAdmin->id,
            'dashboard_url' => $dashboardUrl,
        ]);

        return redirect()->away($dashboardUrl);
    }
}
