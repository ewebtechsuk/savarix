<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Rules\Subdomain as SubdomainRule;
use App\Services\TenantProvisioner;
use App\Support\CompanyIdGenerator;
use App\Support\SubdomainNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class CompanyController extends Controller
{
    public function __construct(private readonly TenantProvisioner $tenantProvisioner)
    {
    }

    public function index()
    {
        $companies = Tenant::all();
        return view('admin.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        $normalizedSubdomain = SubdomainNormalizer::normalize($request->input('subdomain', ''));

        $request->merge(['subdomain' => $normalizedSubdomain]);

        $domain = $normalizedSubdomain !== ''
            ? $this->tenantProvisioner->buildTenantDomain($normalizedSubdomain)
            : null;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => [
                'required',
                'string',
                'max:255',
                new SubdomainRule(),
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
        ]);

        $domain = $this->tenantProvisioner->buildTenantDomain($validated['subdomain']);

        $company_id = CompanyIdGenerator::generate();

        $tenant = Tenant::create([
            'id' => $validated['subdomain'],
        ]);

        $tenant->forceFill([
            'name' => $validated['name'],
            'company_id' => $company_id,
        ])->save();
        $tenant->domains()->create(['domain' => $domain]);

        return redirect()->route('admin.companies.index')->with('success', 'Company created successfully.');
    }

    public function edit($id)
    {
        $company = Tenant::findOrFail($id);
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        $existingDomain = optional($tenant->domains()->first())->domain;

        $normalizedSubdomain = SubdomainNormalizer::normalize($request->input('subdomain', ''));

        $request->merge(['subdomain' => $normalizedSubdomain]);

        $domain = $normalizedSubdomain !== ''
            ? $this->tenantProvisioner->buildTenantDomain($normalizedSubdomain)
            : null;

        $request->validate([
            'subdomain' => [
                'nullable',
                'string',
                'max:255',
                new SubdomainRule(),
                function ($attribute, $value, $fail) use ($domain, $existingDomain) {
                    if ($domain === null) {
                        return;
                    }

                    if (is_string($existingDomain) && strcasecmp($existingDomain, $domain) === 0) {
                        return;
                    }

                    $exists = DB::table('domains')->where('domain', $domain)->exists();

                    if ($exists) {
                        $fail(trans('validation.unique', ['attribute' => $attribute]));
                    }
                },
            ],
            'data' => 'nullable|array',
        ]);
        $data = $tenant->data ?? [];
        // Merge all submitted data fields, but keep existing values if not present in request
        if ($request->has('data')) {
            foreach ($request->input('data') as $key => $value) {
                $data[$key] = $value;
            }
        }
        $tenant->data = $data;
        $tenant->save();
        \Log::info('Company data after save', [
            'id' => $id,
            'tenant_data_after' => $tenant->data,
        ]);
        // Update domain if provided
        if ($request->filled('subdomain')) {
            $updatedSubdomain = (string) $request->input('subdomain', '');
            $updatedDomain = $this->tenantProvisioner->buildTenantDomain($updatedSubdomain);

            $duplicate = DB::table('domains')
                ->where('domain', $updatedDomain)
                ->where('tenant_id', '!=', $tenant->id)
                ->exists();

            if ($duplicate) {
                return back()->withInput()->withErrors([
                    'subdomain' => trans('validation.unique', ['attribute' => 'subdomain']),
                ]);
            }

            if ($tenant->domains()->exists()) {
                $tenant->domains()->update(['domain' => $updatedDomain]);
            } else {
                $tenant->domains()->create(['domain' => $updatedDomain]);
            }
        }
        return redirect()->route('admin.companies.show', $tenant->id)->with('success', 'Company updated successfully.');
    }

    public function destroy($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete();
        return redirect()->route('admin.companies.index')->with('success', 'Company deleted successfully.');
    }

    public function impersonate($id)
    {
        $tenant = Tenant::findOrFail($id);
        tenancy()->initialize($tenant);
        $userModel = config('auth.providers.users.model');
        $adminUser = $userModel::first();
        $domainModel = config('tenancy.domain_model', \Stancl\Tenancy\Database\Models\Domain::class);
        $domain = $domainModel::where('tenant_id', $tenant->id)->first();
        if ($adminUser && $domain) {
            auth()->login($adminUser);
            return redirect()->away('//' . $domain->domain . '/dashboard');
        } elseif (!$domain) {
            return redirect()->back()->with('error', 'No domain found for this company.');
        } else {
            return redirect()->back()->with('error', 'No admin user found for this company.');
        }
    }

    public function users($id)
    {
        $tenant = Tenant::findOrFail($id);
        tenancy()->initialize($tenant);
        $userModel = config('auth.providers.users.model');
        $users = $userModel::all();
        return view('admin.companies.users.index', compact('tenant', 'users'));
    }

    public function billing($id)
    {
        $tenant = Tenant::findOrFail($id);
        tenancy()->initialize($tenant);
        $userModel = config('auth.providers.users.model');
        $users = $userModel::all();
        $userCount = $users->count();
        $freeUsers = 1;
        $additionalUsers = max(0, $userCount - $freeUsers);
        $total = $additionalUsers * 10; // £10 per additional user
        return view('admin.companies.billing.index', compact('tenant', 'users', 'userCount', 'additionalUsers', 'total'));
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

    public function editUser($id, $userId)
    {
        $tenant = Tenant::findOrFail($id);
        tenancy()->initialize($tenant);
        $userModel = config('auth.providers.users.model');
        $user = $userModel::findOrFail($userId);
        return view('admin.companies.users.edit', compact('tenant', 'user'));
    }

    public function updateUser(Request $request, $id, $userId)
    {
        $tenant = Tenant::findOrFail($id);
        tenancy()->initialize($tenant);
        $userModel = config('auth.providers.users.model');
        $user = $userModel::findOrFail($userId);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        $user->save();
        return redirect()->route('admin.companies.users', $id)->with('success', 'User updated successfully.');
    }

    public function deleteUser($id, $userId)
    {
        $tenant = Tenant::findOrFail($id);
        tenancy()->initialize($tenant);
        $userModel = config('auth.providers.users.model');
        $user = $userModel::findOrFail($userId);
        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }

    public function payNow($id)
    {
        $tenant = Tenant::findOrFail($id);
        tenancy()->initialize($tenant);
        $userModel = config('auth.providers.users.model');
        $users = $userModel::all();
        $userCount = $users->count();
        $freeUsers = 1;
        $additionalUsers = max(0, $userCount - $freeUsers);
        $total = $additionalUsers * 10; // £10 per additional user
        if ($total <= 0) {
            return back()->with('info', 'No payment required.');
        }
        Stripe::setApiKey(config('services.stripe.secret'));
        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'gbp',
                    'product_data' => [
                        'name' => 'Additional Users for ' . ($tenant->data['name'] ?? $tenant->id),
                    ],
                    'unit_amount' => 1000, // £10 in pence
                ],
                'quantity' => $additionalUsers,
            ]],
            'mode' => 'payment',
            'success_url' => url('/admin/companies/' . $tenant->id . '/billing?success=1'),
            'cancel_url' => url('/admin/companies/' . $tenant->id . '/billing?cancel=1'),
        ]);
        return redirect($session->url);
    }

    public function paymentHistory($id)
    {
        $tenant = Tenant::findOrFail($id);
        tenancy()->initialize($tenant);
        $payments = DB::table('payments')->orderBy('created_at', 'desc')->get();
        return view('admin.companies.billing.history', compact('tenant', 'payments'));
    }

    public function manualPaymentForm($id)
    {
        $tenant = Tenant::findOrFail($id);
        return view('admin.companies.billing.manual', compact('tenant'));
    }

    public function manualPaymentStore(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        \DB::table('payments')->insert([
            'tenant_id' => $tenant->id,
            'amount' => $request->amount,
            'status' => 'manual_paid',
            'reference' => $request->reference,
            'meta' => json_encode(['note' => $request->note]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('admin.companies.billing.history', $tenant->id)->with('success', 'Manual payment recorded.');
    }

    public function show($id)
    {
        $company = Tenant::findOrFail($id);
        $company->refresh(); // Ensure latest data
        return view('admin.companies.show', compact('company'));
    }
}
