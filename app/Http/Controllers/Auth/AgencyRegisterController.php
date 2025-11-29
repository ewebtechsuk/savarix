<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\Rules\Password;

class AgencyRegisterController extends Controller
{
    public function __construct(private TenantProvisioner $tenantProvisioner)
    {
        $this->middleware('guest');
    }

    public function create(): View
    {
        return view('auth.agency-register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'agency_name' => ['required', 'string', 'max:255'],
            'agency_size' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $subdomain = Str::slug($validated['agency_name']);

        $owner = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $this->tenantProvisioner->provisionFromSignup(
            owner: $owner,
            agencyName: $validated['agency_name'],
            subdomain: $subdomain,
            meta: [
                'agency_size' => $validated['agency_size'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'postcode' => $validated['postcode'] ?? null,
                'password' => $validated['password'],
            ],
        );

        Auth::login($owner);

        return redirect()->route('dashboard')->with('status', 'Welcome to Savarix!');
    }
}
