<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        // Do NOT expose a login form on the main Savarix marketing domain.
        if ($this->isMarketingDomain($request)) {
            abort(404);
        }

        // Only show login when a tenant (agent) is active.
        if (! $this->hasActiveTenant()) {
            abort(404);
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', [], false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * True when this request is for the marketing/main domain
     * where we do NOT want a public login form.
     */
    protected function isMarketingDomain(Request $request): bool
    {
        $host = $request->getHost();

        return in_array($host, ['savarix.com', 'www.savarix.com'], true);
    }

    /**
     * True when a tenancy helper exists and a tenant is bound.
     * This should be the case for agent portals such as example-agent.savarix.com.
     */
    protected function hasActiveTenant(): bool
    {
        if (! function_exists('tenant')) {
            return false;
        }

        return (bool) tenant();
    }
}
