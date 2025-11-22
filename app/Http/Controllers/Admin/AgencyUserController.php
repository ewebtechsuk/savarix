<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AgencyUserController extends Controller
{
    public function index(Agency $agency): View
    {
        return view('admin.agencies.users', [
            'agency' => $agency,
            'users' => $agency->users,
        ]);
    }

    public function store(Request $request, Agency $agency): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:agency_admin,agent,property_manager,viewer'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'agency_id' => $agency->id,
        ]);

        return back();
    }

    public function destroy(Agency $agency, User $user): RedirectResponse
    {
        if ($user->agency_id !== $agency->id) {
            abort(404);
        }

        $user->delete();

        return back();
    }
}
