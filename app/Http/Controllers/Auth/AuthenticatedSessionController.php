<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        if ($this->shouldHideLoginForm($request->getHost())) {
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

    private function shouldHideLoginForm(?string $host): bool
    {
        if (! is_string($host) || $host === '') {
            return false;
        }

        $host = Str::lower($host);

        $marketingDomains = array_map(
            static fn (string $domain): string => Str::lower($domain),
            config('marketing.domains', [])
        );

        foreach ($marketingDomains as $domain) {
            if ($domain === '') {
                continue;
            }

            if (Str::is($domain, $host)) {
                return true;
            }
        }

        return false;
    }

}
