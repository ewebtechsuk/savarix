<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImpersonationController extends Controller
{
    public function stop(Request $request): RedirectResponse
    {
        $session = $request->session();
        $ownerId = (int) $session->get('impersonator_id');
        $wasImpersonating = (bool) $session->pull('impersonating', false);

        $session->forget([
            'impersonator_id',
            'impersonated_agency_id',
            'impersonated_user_id',
        ]);

        if (! $wasImpersonating || ! $ownerId) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->with('status', 'Impersonation session cleared. Please log in again.');
        }

        $centralConnection = config('tenancy.database.central_connection', config('database.default'));
        DB::setDefaultConnection($centralConnection);

        $owner = User::on($centralConnection)->find($ownerId);

        if (! $owner || ! $owner->isOwner()) {
            Log::warning('Unable to restore owner after impersonation stop', [
                'impersonator_id' => $ownerId,
                'impersonated_user_id' => $request->user()?->id,
            ]);

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->with('error', 'Original owner session not found. Please log in again.');
        }

        Auth::shouldUse('web');
        Auth::guard('web')->login($owner);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('status', 'Impersonation ended.');
    }
}
