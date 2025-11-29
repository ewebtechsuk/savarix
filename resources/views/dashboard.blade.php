@extends('layouts.agency')

@section('content')
<div class="space-y-6">
    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-500">Active Listings</h3>
                <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">Live</span>
            </div>
            <div class="mt-3 text-3xl font-semibold text-gray-900">{{ number_format($metrics['active_listings'] ?? 0) }}</div>
            <p class="mt-1 text-sm text-gray-500">Published and available properties</p>
        </div>
        <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-500">Upcoming Viewings</h3>
                <span class="text-xs text-indigo-600 bg-indigo-50 px-2 py-1 rounded-full">Today</span>
            </div>
            <div class="mt-3 text-3xl font-semibold text-gray-900">{{ number_format($metrics['upcoming_viewings'] ?? 0) }}</div>
            <p class="mt-1 text-sm text-gray-500">Synced with today’s diary</p>
        </div>
        <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-500">Offers Pending</h3>
                <span class="text-xs text-yellow-600 bg-yellow-50 px-2 py-1 rounded-full">Awaiting action</span>
            </div>
            <div class="mt-3 text-3xl font-semibold text-gray-900">{{ number_format($metrics['pending_offers'] ?? 0) }}</div>
            <p class="mt-1 text-sm text-gray-500">Negotiations in progress</p>
        </div>
        <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-500">Active Tenancies</h3>
                <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">Lettings</span>
            </div>
            <div class="mt-3 text-3xl font-semibold text-gray-900">{{ number_format($metrics['active_tenancies'] ?? 0) }}</div>
            <p class="mt-1 text-sm text-gray-500">Live tenancies under management</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow-sm rounded-xl border border-gray-100">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Today’s Viewings</h3>
                        <p class="text-sm text-gray-500">Keep an eye on punctuality and follow-ups.</p>
                    </div>
                    <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">View calendar</a>
                </div>
                <div class="divide-y">
                    @forelse($viewings as $viewing)
                        <div class="px-5 py-4 flex items-center justify-between">
                            <div class="flex items-start space-x-4">
                                <div class="w-16 text-sm font-semibold text-indigo-600">{{ optional($viewing->date)->format('H:i') }}</div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $viewing->property->title ?? 'Property #' . $viewing->property_id }}</div>
                                    <p class="text-sm text-gray-500">{{ $viewing->contact->name ?? 'Applicant' }} · {{ optional($viewing->date)->format('j M') }}</p>
                                </div>
                            </div>
                            <span class="text-xs px-3 py-1 rounded-full bg-green-50 text-green-700">Scheduled</span>
                        </div>
                    @empty
                        <div class="px-5 py-4 text-sm text-gray-500">No viewings scheduled for today.</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-100">
                <div class="flex items-center justify-between px-5 py-4 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                        <p class="text-sm text-gray-500">Offers, notes and tasks across the team.</p>
                    </div>
                    <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">View all</a>
                </div>
                <div class="px-5 py-4 space-y-4">
                    @foreach([
                        ['title' => 'Offer received', 'description' => '£615k for 12 The Avenues', 'by' => 'Sarah K.', 'time' => '12m ago'],
                        ['title' => 'Viewing feedback', 'description' => 'Positive feedback on Riverside Quay 3B', 'by' => 'Liam P.', 'time' => '34m ago'],
                        ['title' => 'Task completed', 'description' => 'Photography booked for Maple Street Cottage', 'by' => 'Ethan T.', 'time' => '1h ago'],
                        ['title' => 'New enquiry', 'description' => 'High intent lead for Penthouse 27', 'by' => 'Portal Sync', 'time' => '2h ago'],
                    ] as $activity)
                        <div class="flex items-start space-x-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
                            <div>
                                <div class="flex items-center justify-between">
                                    <p class="font-medium text-gray-900">{{ $activity['title'] }}</p>
                                    <span class="text-xs text-gray-400">{{ $activity['time'] }}</span>
                                </div>
                                <p class="text-sm text-gray-600">{{ $activity['description'] }}</p>
                                <p class="text-xs text-gray-400">{{ $activity['by'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white shadow-sm rounded-xl border border-gray-100">
                <div class="px-5 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                    <p class="text-sm text-gray-500">Jump straight into the most common workflows.</p>
                </div>
                <div class="px-5 py-4 space-y-3">
                    @foreach($quickActions as $action)
                        <a href="{{ $action['href'] }}" class="flex items-center justify-between px-4 py-3 rounded-lg border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50">
                            <div>
                                <p class="font-medium text-gray-900">{{ $action['label'] }}</p>
                                <p class="text-sm text-gray-500">{{ $action['description'] }}</p>
                            </div>
                            <span class="text-indigo-600">→</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-100">
                <div class="px-5 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Team Focus</h3>
                    <p class="text-sm text-gray-500">Owners with the highest activity this week.</p>
                </div>
                <div class="px-5 py-4 space-y-4">
                    @foreach([
                        ['name' => 'Ava Johnson', 'role' => 'Lead Negotiator', 'metric' => '18 viewings', 'trend' => '+12%'],
                        ['name' => 'Ethan Turner', 'role' => 'Sales Associate', 'metric' => '11 offers', 'trend' => '+5%'],
                        ['name' => 'Sophia Patel', 'role' => 'Lettings', 'metric' => '9 leases', 'trend' => '+9%'],
                    ] as $member)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $member['name'] }}</p>
                                <p class="text-sm text-gray-500">{{ $member['role'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-indigo-700">{{ $member['metric'] }}</p>
                                <p class="text-xs text-emerald-600">{{ $member['trend'] }} vs last week</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
