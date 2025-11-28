@extends('layouts.agency')

@section('content')
    @php
        $metrics = [
            ['title' => 'Active listings', 'value' => '128', 'change' => '+12.4%', 'trend' => 'up', 'subtitle' => 'vs last month'],
            ['title' => 'Upcoming viewings', 'value' => '24', 'change' => '8 today', 'trend' => 'neutral', 'subtitle' => 'scheduled'],
            ['title' => 'Applications received', 'value' => '56', 'change' => '+4.1%', 'trend' => 'up', 'subtitle' => 'this week'],
            ['title' => 'Average response time', 'value' => '1.2h', 'change' => '-15m', 'trend' => 'down', 'subtitle' => 'last 24h'],
        ];

        $viewings = [
            ['property' => '2B Shoreline Apartments', 'client' => 'Amira Khan', 'time' => 'Today, 10:30 AM', 'agent' => 'Danielle', 'status' => 'Confirmed'],
            ['property' => '14 Kingfisher Drive', 'client' => 'Marcus Lee', 'time' => 'Today, 1:00 PM', 'agent' => 'Elliot', 'status' => 'Awaiting'],
            ['property' => '77 Riverside Walk', 'client' => 'Priya Patel', 'time' => 'Today, 3:15 PM', 'agent' => 'Hannah', 'status' => 'Confirmed'],
        ];

        $activities = [
            ['title' => 'New application received', 'detail' => 'Flat 3, Oakwood Heights', 'time' => '10 minutes ago'],
            ['title' => 'Viewing rescheduled', 'detail' => '12 Market Street moved to Thu 2 PM', 'time' => '1 hour ago'],
            ['title' => 'Offer submitted', 'detail' => '4B Parkside Residences', 'time' => 'Yesterday'],
        ];

        $teamFocus = [
            ['name' => 'Danielle Frost', 'metric' => '8 viewings', 'focus' => 'New build launches'],
            ['name' => 'Elliot Barnes', 'metric' => '12 leads', 'focus' => 'Prime rentals'],
            ['name' => 'Hannah Mills', 'metric' => '5 offers', 'focus' => 'City centre'],
        ];
    @endphp

    <div class="space-y-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($metrics as $metric)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm text-gray-500">{{ $metric['title'] }}</p>
                            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $metric['value'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $metric['subtitle'] }}</p>
                        </div>
                        @php
                            $indicatorClass = [
                                'up' => 'text-emerald-600 bg-emerald-50',
                                'down' => 'text-rose-600 bg-rose-50',
                                'neutral' => 'text-gray-600 bg-gray-100',
                            ][$metric['trend']] ?? 'text-gray-600 bg-gray-100';
                        @endphp
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $indicatorClass }}">{{ $metric['change'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
                <div class="flex items-center justify-between px-6 py-4 border-b">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Today’s viewings</h2>
                        <p class="text-sm text-gray-500">Quick glance at scheduled appointments</p>
                    </div>
                    <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View calendar</a>
                </div>
                <div class="divide-y">
                    @foreach ($viewings as $viewing)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $viewing['property'] }}</p>
                                <p class="text-sm text-gray-500">{{ $viewing['client'] }} • {{ $viewing['time'] }}</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-500">{{ $viewing['agent'] }}</span>
                                @php
                                    $statusClasses = [
                                        'Confirmed' => 'bg-emerald-50 text-emerald-700',
                                        'Awaiting' => 'bg-amber-50 text-amber-700',
                                    ];
                                @endphp
                                <span class="px-3 py-1 text-xs font-medium rounded-full {{ $statusClasses[$viewing['status']] ?? 'bg-gray-100 text-gray-700' }}">{{ $viewing['status'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold text-gray-900">Recent activity</h2>
                    <p class="text-sm text-gray-500">Latest updates across your agency</p>
                </div>
                <div class="divide-y">
                    @foreach ($activities as $activity)
                        <div class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ $activity['title'] }}</p>
                            <p class="text-sm text-gray-500">{{ $activity['detail'] }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $activity['time'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Quick actions</h2>
                        <p class="text-sm text-gray-500">Frequently used tasks</p>
                    </div>
                    <span class="text-xs text-gray-400">Shortcuts</span>
                </div>
                <div class="p-4 space-y-3">
                    <a href="#" class="flex items-center justify-between px-4 py-3 rounded-lg border border-gray-200 hover:border-indigo-200 hover:bg-indigo-50">
                        <div>
                            <p class="font-medium text-gray-900">Add new listing</p>
                            <p class="text-sm text-gray-500">Create a property in minutes</p>
                        </div>
                        <span class="text-indigo-600 text-lg">+</span>
                    </a>
                    <a href="#" class="flex items-center justify-between px-4 py-3 rounded-lg border border-gray-200 hover:border-indigo-200 hover:bg-indigo-50">
                        <div>
                            <p class="font-medium text-gray-900">Schedule viewing</p>
                            <p class="text-sm text-gray-500">Find a slot that suits everyone</p>
                        </div>
                        <span class="text-indigo-600 text-lg">&#128197;</span>
                    </a>
                    <a href="#" class="flex items-center justify-between px-4 py-3 rounded-lg border border-gray-200 hover:border-indigo-200 hover:bg-indigo-50">
                        <div>
                            <p class="font-medium text-gray-900">Send update</p>
                            <p class="text-sm text-gray-500">Notify applicants or landlords</p>
                        </div>
                        <span class="text-indigo-600 text-lg">&#9993;</span>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Team focus</h2>
                        <p class="text-sm text-gray-500">Where effort is going this week</p>
                    </div>
                    <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Team board</a>
                </div>
                <div class="divide-y">
                    @foreach ($teamFocus as $member)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $member['name'] }}</p>
                                <p class="text-sm text-gray-500">{{ $member['focus'] }}</p>
                            </div>
                            <span class="text-sm font-medium text-indigo-600">{{ $member['metric'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
