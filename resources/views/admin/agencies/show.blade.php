@extends('admin.layouts.app')

@section('breadcrumb')
Agencies / {{ $agency->name }}
@endsection

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl bg-gray-800 p-6 shadow-lg border border-gray-700">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold text-white">{{ $agency->name }}</h1>
                <p class="text-sm text-gray-400">Independent estate agency in London</p>
                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-gray-300">
                    <div class="space-y-1">
                        <p class="text-xs uppercase tracking-wide text-gray-400">Email</p>
                        <p>{{ $agency->email ?? 'Not provided' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs uppercase tracking-wide text-gray-400">Phone</p>
                        <p>{{ $agency->phone ?? 'Not provided' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs uppercase tracking-wide text-gray-400">Created</p>
                        <p>{{ optional($agency->created_at)->format('d M Y') }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs uppercase tracking-wide text-gray-400">Status</p>
                        @php($statusClasses = $agency->status === 'active' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400')
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                            {{ ucfirst($agency->status) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="#" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-gray-100 border border-gray-700 hover:border-gray-500">Edit details</a>
                <a href="#" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-gray-100 border border-gray-700 hover:border-gray-500">Open in tenant app</a>
                <button class="rounded-lg border border-red-500/60 px-4 py-2 text-sm font-semibold text-red-300 hover:bg-red-500/10">Disable Agency</button>
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-gray-800 px-4 pt-3 shadow-lg border border-gray-700">
        <div class="flex flex-wrap items-center gap-2 border-b border-gray-700">
            <a class="px-3 py-3 text-sm font-semibold text-white border-b-2 border-yellow-400">Overview</a>
            <a href="{{ route('admin.agencies.users.index', $agency->id) }}" class="px-3 py-3 text-sm font-semibold text-gray-400 hover:text-white">Users</a>
            <button class="px-3 py-3 text-sm font-semibold text-gray-500 cursor-not-allowed">Tenancy / Subscription</button>
            <button class="px-3 py-3 text-sm font-semibold text-gray-500 cursor-not-allowed">Activity log</button>
        </div>

        <div class="p-4 lg:p-6 space-y-6">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-gray-700 bg-gray-900 p-4 shadow-inner">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Primary contact</p>
                    <h3 class="mt-2 text-lg font-semibold text-white">{{ $agency->name }}</h3>
                    <p class="text-sm text-gray-300">{{ $agency->email ?? 'No email on file' }}</p>
                    <p class="text-sm text-gray-300">{{ $agency->phone ?? 'No phone on file' }}</p>
                </div>
                <div class="rounded-xl border border-gray-700 bg-gray-900 p-4 shadow-inner">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Status</p>
                    <h3 class="mt-2 text-lg font-semibold text-white">{{ ucfirst($agency->status) }}</h3>
                    <p class="text-sm text-gray-300">Control access for this agency when needed.</p>
                </div>
            </div>
            <div class="rounded-xl border border-gray-700 bg-gray-900 p-4 shadow-inner">
                <p class="text-xs uppercase tracking-wide text-gray-400">Notes</p>
                <p class="mt-2 text-sm text-gray-300">Use this space to capture important context about the agency, onboarding progress, and health.</p>
            </div>
        </div>
    </div>
</div>
@endsection
