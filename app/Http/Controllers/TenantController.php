<?php
namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantProvisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function __construct(private readonly TenantProvisioner $tenantProvisioner)
    {
    }

    public function index()
    {
        $tenants = Tenant::all();
        return view('tenants.index', compact('tenants'));
    }

    public function show($id)
    {
        $tenant = Tenant::findOrFail($id);
        // Load users for this tenant (if tenancy is initialized)
        $users = [];
        try {
            tenancy()->initialize($tenant);
            $userModel = config('auth.providers.users.model');
            $users = $userModel::all();
        } catch (\Exception $e) {
            // If tenancy not initialized, leave users empty
        }
        return view('tenants.show', compact('tenant', 'users'));
    }

    public function create()
    {
        return view('tenants.create');
    }

    public function store(Request $request)
    {
        Log::info('Tenant store request', $request->all());

        $normalizedSubdomain = (string) Str::of((string) $request->input('subdomain', ''))
            ->trim()
            ->trim('.')
            ->lower()
            ->trim();

        $request->merge(['subdomain' => $normalizedSubdomain]);

        $domain = $normalizedSubdomain !== ''
            ? $this->tenantProvisioner->buildTenantDomain($normalizedSubdomain)
            : null;

        $validated = $request->validate([
            'subdomain' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($domain) {
                    if ($domain === null) {
                        return;
                    }

                    $exists = DB::table('domains')->where('domain', $domain)->exists();

                    if ($exists) {
                        $fail(trans('validation.unique', ['attribute' => $attribute]));
                    }
                },
            ],
            'data' => 'nullable|array',
            'user' => 'nullable|array',
        ]);
        $result = $this->tenantProvisioner->provision([
            'subdomain' => $validated['subdomain'],
            'data' => $request->input('data', []),
            'user' => $request->input('user', []),
            'name' => $request->input('name'),
            'company_name' => $request->input('company_name'),
        ]);

        return redirect()
            ->route('tenants.index')
            ->with($result->flashLevel(), $result->message() !== '' ? $result->message() : 'Tenant provisioning completed.')
            ->with('provisioning', $result->toArray());
    }

    public function edit($id)
    {
        $tenant = Tenant::findOrFail($id);
        // Load users for this tenant (if tenancy is initialized)
        $users = [];
        try {
            tenancy()->initialize($tenant);
            $userModel = config('auth.providers.users.model');
            $users = $userModel::all();
        } catch (\Exception $e) {
            // If tenancy not initialized, leave users empty
        }
        return view('tenants.edit', compact('tenant', 'users'));
    }

    public function update(Request $request, $id)
    {
        Log::info('Tenant update request', $request->all());

        $request->validate([
            'subdomain' => 'nullable|string|max:255',
            'data' => 'nullable|array',
        ]);
        $tenant = Tenant::findOrFail($id);
        $data = is_array($tenant->data) ? $tenant->data : [];
        if ($request->has('data')) {
            foreach ($request->input('data') as $key => $value) {
                $data[$key] = $value;
            }
        }
        // fallback for name
        if ($request->has('name')) {
            $data['name'] = $request->input('name');
        }
        if ($request->has('company_name')) {
            $data['company_name'] = $request->input('company_name');
        }
        $tenant->data = $data;
        $tenant->save();
        $tenant->refresh(); // Ensure latest data is loaded
        if ($request->filled('subdomain')) {
            $domain = $this->tenantProvisioner->buildTenantDomain((string) $request->input('subdomain', ''));

            if ($tenant->domains()->exists()) {
                $tenant->domains()->update(['domain' => $domain]);
            } else {
                $tenant->domains()->create(['domain' => $domain]);
            }
        }
        return redirect()->route('tenants.show', $tenant->id)->with('success', 'Tenant updated successfully.');
    }

    public function delete($id)
    {
        $tenant = Tenant::findOrFail($id);
        return view('tenants.delete', compact('tenant'));
    }

    public function destroy($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete();
        return redirect()->route('tenants.index')->with('success', 'Tenant deleted successfully.');
    }

    public function addUser(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        tenancy()->initialize($tenant);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);
        $userModel = config('auth.providers.users.model');
        $userModel::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        return back()->with('success', 'User added successfully.');
}

}
