<x-app-layout>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Welcome back, {{ $dashboard->tenantDisplayName() }}!</h1>
            <p class="text-sm text-gray-500">Here's the latest activity across your tenancy.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="bg-white shadow-sm rounded-xl border border-gray-100">
                <div class="px-5 py-4 flex items-center justify-between border-b">
                    <span class="font-semibold text-gray-900">Upcoming rent</span>
                    <span class="text-sm font-semibold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full">{{ $dashboard->formattedUpcomingRentTotal() }}</span>
                </div>
                <div class="divide-y">
                    @forelse ($dashboard->upcomingPayments() as $payment)
                        <div class="px-5 py-4">
                            <div class="font-semibold text-gray-900">{{ $payment->tenancy?->property?->title ?? 'Tenancy #' . $payment->tenancy_id }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $dashboard->formatCurrency((float) $payment->amount) }} ·
                                Due {{ optional($payment->created_at)->format('j M Y') ?? 'soon' }}
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-4 text-sm text-gray-500">No upcoming rent payments.</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-100">
                <div class="px-5 py-4 border-b font-semibold text-gray-900">Open maintenance</div>
                <div class="divide-y">
                    @forelse ($dashboard->maintenanceRequests() as $request)
                        <div class="px-5 py-4">
                            <div class="font-semibold text-gray-900">{{ $request->property?->title ?? 'Maintenance request #' . $request->id }}</div>
                            <div class="text-sm text-gray-500">{{ ucfirst($request->status) }} · logged {{ optional($request->created_at)->format('j M Y') }}</div>
                            <p class="text-sm text-gray-600 mb-0">{{ \Illuminate\Support\Str::limit($request->description, 90) }}</p>
                        </div>
                    @empty
                        <div class="px-5 py-4 text-sm text-gray-500">No open maintenance requests 🎉</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-100">
                <div class="px-5 py-4 border-b font-semibold text-gray-900">Recent communications</div>
                <div class="divide-y">
                    @forelse ($dashboard->communications() as $communication)
                        <div class="px-5 py-4">
                            <div class="font-semibold text-gray-900">{{ $communication->contact?->name ?? 'Contact #' . $communication->contact_id }}</div>
                            <div class="text-sm text-gray-500">{{ optional($communication->created_at)->format('j M Y H:i') }}</div>
                            <p class="text-sm text-gray-600 mb-0">{{ \Illuminate\Support\Str::limit($communication->communication, 110) }}</p>
                        </div>
                    @empty
                        <div class="px-5 py-4 text-sm text-gray-500">No recent messages to show.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-xl border border-gray-100">
            <div class="px-5 py-4 border-b font-semibold text-gray-900">Tenancy overview</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start date</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End date</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rent</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($dashboard->tenancies() as $tenancy)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $tenancy->property?->title ?? 'Property #' . $tenancy->property_id }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $tenancy->contact?->name ?? 'Contact #' . $tenancy->contact_id }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ optional($tenancy->start_date)->format('j M Y') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ optional($tenancy->end_date)->format('j M Y') ?? 'Ongoing' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $dashboard->formatCurrency((float) $tenancy->rent_amount) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ ucfirst($tenancy->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">No tenancy records available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
