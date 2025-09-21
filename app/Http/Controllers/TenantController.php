<?php
namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TenantController extends Controller
{
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

        $request->validate([
            'subdomain' => 'required|string|max:255|unique:domains,domain',
            'data' => 'nullable|array',
            'user' => 'nullable|array',
        ]);
        do {
            $company_id = random_int(100000, 999999);
        } while (Tenant::where('data->company_id', $company_id)->exists());
        $data = $request->input('data', []);
        if (!is_array($data)) {
            $data = [];
        }
        $data['company_id'] = $company_id;
        // fallback for name
        if (!isset($data['company_name']) && isset($request->name)) {
            $data['company_name'] = $request->name;
        }
        if (!isset($data['name']) && isset($request->name)) {
            $data['name'] = $request->name;
        }
        $tenant = Tenant::create([
            'id' => $request->subdomain,
            'data' => $data,
        ]);

        $tenant->domains()->create([
            'domain' => $this->buildTenantDomain((string) $request->input('subdomain', '')),
        ]);
        // Create initial user if provided
        if ($request->has('user.name') && $request->has('user.email') && $request->has('user.password')) {
            try {
                tenancy()->initialize($tenant);
                $userModel = config('auth.providers.users.model');
                $userModel::create([
                    'name' => $request->input('user.name'),
                    'email' => $request->input('user.email'),
                    'password' => bcrypt($request->input('user.password')),
                ]);
            } catch (\Exception $e) {
                // Ignore user creation errors for now
            }
        }
        return redirect()->route('tenants.index')->with('success', 'Tenant created successfully.');
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
            $domain = $this->buildTenantDomain((string) $request->input('subdomain', ''));

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

    private function buildTenantDomain(string $subdomain): string
    {
        $subdomain = trim($subdomain);

        if ($subdomain === '') {
            return $this->centralHost();
        }

        $host = $this->centralHost();

        return Str::of($subdomain)->trim('.')->append('.' . $host);
    }

    private function centralHost(): string
    {
        $appUrl = config('app.url');
        $host = parse_url($appUrl ?: '', PHP_URL_HOST);

        if (is_string($host) && $host !== '') {
            return $host;
        }

        $centralDomains = collect(config('tenancy.central_domains', []));

        $nonIpDomain = $centralDomains->first(function ($domain) {
            return is_string($domain) && !filter_var($domain, FILTER_VALIDATE_IP);
        });

        if (is_string($nonIpDomain) && $nonIpDomain !== '') {
            return $nonIpDomain;
        }

        $first = $centralDomains->first();

        if (is_string($first) && $first !== '') {
            return $first;
        }

        return 'localhost';
    }
}
