<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * Registration is disabled for Savarix (no /register page).
     */
    public function create(): View
    {
        abort(404);
    }

    /**
     * Handle an incoming registration request.
     *
     * Registration is disabled for Savarix (no /register endpoint).
     */
    public function store(Request $request): RedirectResponse
    {
        abort(404);
    }
}
