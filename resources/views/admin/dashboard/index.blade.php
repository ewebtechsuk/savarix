@extends('admin.layouts.app')

@section('breadcrumb')

@endsection

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
            <h1 class="text-3xl font-bold text-white">Dashboard</h1>
            <p class="text-sm text-gray-400">Owner overview of all agencies in Savarix.</p>
        </div>
        <a href="{{ route('admin.agencies.index') }}#create-agency" class="inline-flex items-center justify-center rounded-lg bg-yellow-400 px-4 py-2 text-sm font-semibold text-black shadow hover:bg-yellow-300">
            + Add Agency
        </a>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-gray-800 p-5 shadow-lg border border-gray-700">
            <p class="text-xs uppercase tracking-wide text-gray-400">Total Agencies</p>
            <p class="mt-3 text-4xl font-semibold text-white">{{ number_format($agencyCount) }}</p>
            <p class="text-sm text-gray-500">All agencies in system</p>
        </div>
        <div class="rounded-2xl bg-gray-800 p-5 shadow-lg border border-gray-700">
            <p class="text-xs uppercase tracking-wide text-gray-400">Active Agencies</p>
            <p class="mt-3 text-4xl font-semibold text-white">{{ number_format($activeAgencies) }}</p>
            <p class="text-sm text-gray-500">Currently enabled and able to login</p>
        </div>
        <div class="rounded-2xl bg-gray-800 p-5 shadow-lg border border-gray-700">
            <p class="text-xs uppercase tracking-wide text-gray-400">Total Agency Users</p>
            <p class="mt-3 text-4xl font-semibold text-white">{{ number_format($totalAgencyUsers) }}</p>
            <p class="text-sm text-gray-500">Agents &amp; admins</p>
        </div>
        <div class="rounded-2xl bg-gray-800 p-5 shadow-lg border border-gray-700">
            <p class="text-xs uppercase tracking-wide text-gray-400">Last Activity</p>
            @if($lastActivity)
                <p class="mt-3 text-lg font-semibold text-white">{{ $lastActivity['type'] }}</p>
                <p class="text-sm text-gray-400">{{ $lastActivity['agency'] }} – {{ $lastActivity['description'] }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $lastActivity['time']->diffForHumans() }}</p>
            @else
                <p class="mt-3 text-lg font-semibold text-white">No activity yet</p>
                <p class="text-sm text-gray-500">Create an agency to get started.</p>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl bg-gray-800 p-6 shadow-lg border border-gray-700 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-400">Recent activity</p>
                    <h2 class="text-lg font-semibold text-white">Latest events</h2>
                </div>
                <span class="text-xs text-gray-500">Last 5 updates</span>
            </div>
            <div class="divide-y divide-gray-700">
                @forelse($recentActivity as $event)
                    <div class="py-3 flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-semibold text-white">{{ $event['agency'] }}</p>
                            <p class="text-sm text-gray-300">{{ $event['type'] }} – {{ $event['description'] }}</p>
                        </div>
                        <span class="text-xs text-gray-500">{{ $event['time']->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="py-3 text-sm text-gray-500">No recent events yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl bg-gray-800 p-6 shadow-lg border border-gray-700">
            <p class="text-xs uppercase tracking-wide text-gray-400">Quick actions</p>
            <div class="mt-4 space-y-3 text-sm">
                <a href="{{ route('admin.agencies.index') }}#create-agency" class="flex items-center justify-between rounded-lg bg-yellow-400 px-4 py-2 font-semibold text-black shadow hover:bg-yellow-300">
                    <span>Create new agency</span>
                    <span>→</span>
                </a>
                <a href="{{ route('admin.agencies.index') }}" class="flex items-center justify-between rounded-lg bg-gray-900 px-4 py-2 text-gray-100 border border-gray-700 hover:border-gray-500">
                    <span>Invite new agency admin</span>
                    <span class="text-gray-400">→</span>
                </a>
                <a href="#" class="flex items-center justify-between rounded-lg bg-gray-900 px-4 py-2 text-gray-100 border border-gray-700 hover:border-gray-500">
                    <span>Open agency app</span>
                    <span class="text-gray-400">→</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
