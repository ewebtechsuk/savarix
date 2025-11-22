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
use Illuminate\Support\Str;

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
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'domain' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,suspended,trial'],
        ]);

        $agency->update($data);

        return back();
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
        try {
            $agencyAdmin = $agency->users()
                ->where('role', 'agency_admin')
                ->orderBy('id')
                ->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            Log::warning('Agency admin not found for impersonation', ['agency_id' => $agency->id]);

            return back()->with('error', 'No agency admin user exists for this agency.');
        }

        $ownerId = Auth::id();
        $session = $request->session();

        $session->put([
            'impersonating' => true,
            'impersonator_id' => $ownerId,
            'impersonated_agency_id' => $agency->id,
            'impersonated_user_id' => $agencyAdmin->id,
        ]);

        Auth::shouldUse('web');
        Auth::guard('web')->login($agencyAdmin);

        $session->regenerate();

        try {
            $dashboardUrl = $agency->tenantDashboardUrl();

            if (! $dashboardUrl) {
                Log::warning('Impersonation redirect missing domain', ['agency_id' => $agency->id]);

                return back()->with('error', 'Set a domain on the agency before impersonating.');
            }

            Log::info('Impersonation login redirecting to tenant', [
                'agency_id' => $agency->id,
                'impersonated_user_id' => $agencyAdmin->id,
                'dashboard_url' => $dashboardUrl,
            ]);

            return redirect()->away($dashboardUrl);
        } catch (\Throwable $exception) {
            Log::error('Failed to redirect after impersonation', [
                'agency_id' => $agency->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Unable to redirect into the tenant app.');
        }
    }
}
