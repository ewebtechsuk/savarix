<x-guest-layout>
    <div class="max-w-3xl mx-auto mt-12">
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-8">
            <h1 class="text-2xl font-semibold text-gray-900 mb-2">Tenant Directory</h1>
            <p class="text-sm text-gray-600 mb-6">The following tenants are currently registered in Savarix:</p>

            <div class="space-y-4">
                @forelse ($tenants as $tenant)
                    <div class="border border-gray-100 rounded-lg p-4">
                        <div class="font-semibold text-gray-900">{{ $tenant['name'] }}</div>
                        <div class="text-sm text-gray-500">Slug: {{ $tenant['slug'] }}</div>
                        <ul class="mt-2 space-y-1 text-sm text-gray-600 list-disc list-inside">
                            @foreach ($tenant['domains'] as $domain)
                                <li>{{ $domain }}</li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No tenants are registered yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-guest-layout>
