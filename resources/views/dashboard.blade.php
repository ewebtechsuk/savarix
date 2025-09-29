<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}

                    @php
                        $tenantCollection = collect(\App\Tenancy\TenantRepositoryManager::getRepository()->allTenants());
                        $company = $tenantCollection->firstWhere('slug', 'aktonz');
                    @endphp

                    @if($company)
                        <div class="mt-6">
                            <h3 class="text-lg font-bold">Welcome to {{ $company['name'] }}!</h3>
                            <div class="mt-2 text-green-600">Company profile found for Aktonz.</div>
                            <pre>{{ json_encode($company, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @else
                        <div class="mt-6 text-red-600">No company profile found for Aktonz.</div>
                    @endif

                    <div class="mt-6">
                        <h3 class="text-lg font-bold">Debug: All Tenants With Data</h3>
                        <ul>
                        @forelse($tenantCollection as $tenant)
                            <li>
                                <strong>Slug:</strong> {{ $tenant['slug'] }}<br>
                                <strong>Name:</strong> {{ $tenant['name'] }}<br>
                                <strong>Domains:</strong> {{ implode(', ', $tenant['domains']) }}
                            </li>
                        @empty
                            <li>No tenants are registered yet.</li>
                        @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
