<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $agency = Agency::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'domain' => $data['domain'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        // Auto-provision tenant + domain when an agency is created with a domain.
        $this->syncTenantFromAgency($agency);

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

            // Keep Stancl tenant + domain in sync after updates.
            $this->syncTenantFromAgency($agency);
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

    public function impersonate(Agency $agency): RedirectResponse
    {
        // Whatever logic you already have to pick the agency admin user
        // and log them in should stay; we just normalize the redirect.

        $dashboardUrl = $agency->tenantDashboardUrl();

        if (! $dashboardUrl) {
            return back()->with('error', 'Agency does not have a valid tenant domain configured.');
        }

        // Example – keep your existing auth + session logic here:
        //
        // Auth::login($agencyAdminUser);
        // session(['impersonating_agency_id' => $agency->id]);

        return redirect()->away($dashboardUrl);
    }

    /**
     * Ensure a Stancl Tenancy tenant and domain exist for the given agency.
     *
     * This keeps the Tenant model, its data payload, and the domains table
     * in sync with the central agencies table whenever the owner sets
     * or updates the agency's domain.
     */
    protected function syncTenantFromAgency(Agency $agency): void
    {
        if (! $agency->domain) {
            return;
        }

        $tenantId = $agency->slug ?: (string) $agency->id;

        /** @var \App\Models\Tenant $tenant */
        $tenant = Tenant::firstOrCreate(
            ['id' => $tenantId],
            [
                'name' => $agency->name,
                'data' => [
                    'slug' => $agency->slug,
                    'company_name' => $agency->name,
                    'company_email' => $agency->email,
                    'company_id' => (string) $agency->id,
                    'domains' => [$agency->domain],
                ],
            ]
        );

        // Keep tenant metadata aligned with the agency.
        $data = (array) $tenant->data;
        $data['slug'] = $agency->slug;
        $data['company_name'] = $agency->name;
        $data['company_email'] = $agency->email;
        $data['company_id'] = (string) $agency->id;
        $data['domains'] = [$agency->domain];

        $tenant->update([
            'name' => $agency->name,
            'data' => $data,
        ]);

        // Ensure a Domain record exists and is linked.
        $tenant->domains()->updateOrCreate(
            ['domain' => $agency->domain],
            []
        );
    }
}
