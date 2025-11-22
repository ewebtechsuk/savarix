<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        ]);

        Agency::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'domain' => $data['domain'] ?? null,
            'status' => 'active',
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
        if (! $agency->domain) {
            return redirect()->away(config('app.url'));
        }

        $domain = trim($agency->domain);

        if (! str_starts_with($domain, 'http://') && ! str_starts_with($domain, 'https://')) {
            $domain = 'https://' . $domain;
        }

        return redirect()->away(rtrim($domain, '/') . '/dashboard');
    }

    public function impersonate(Agency $agency): RedirectResponse
    {
        $agencyAdmin = $agency->users()
            ->where('role', 'agency_admin')
            ->orderBy('id')
            ->firstOrFail();

        $ownerId = Auth::id();
        session([
            'impersonating' => true,
            'impersonator_id' => $ownerId,
        ]);

        Auth::guard('web')->login($agencyAdmin);

        $domain = $agency->domain ?: parse_url(config('app.url'), PHP_URL_HOST);

        if (! str_starts_with($domain, 'http://') && ! str_starts_with($domain, 'https://')) {
            $domain = 'https://' . $domain;
        }

        return redirect()->away(rtrim($domain, '/') . '/dashboard');
    }
}
