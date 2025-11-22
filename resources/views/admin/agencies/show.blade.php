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
                <a href="#agency-settings" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-gray-100 border border-gray-700 hover:border-gray-500">Edit details</a>
                @if($agency->domain)
                    <a href="{{ route('admin.agencies.open', $agency->id) }}"
                       class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-gray-100 border border-gray-700 hover:border-gray-500">
                        Open in tenant app
                    </a>
                @else
                    <button type="button"
                            class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-gray-400 border border-gray-700 cursor-not-allowed"
                            title="Set a domain to enable this">
                        Open in tenant app
                    </button>
                @endif
                <form action="{{ route('admin.agencies.impersonate', $agency->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-gray-100 border border-gray-700 hover:border-gray-500">Impersonate</button>
                </form>
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
                    <p class="text-xs uppercase tracking-wide text-gray-400">Domain</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ $agency->domain ?? 'No domain set' }}</p>
                    @php($tenantDashboardUrl = $agency->tenantDashboardUrl())
                    <p class="text-sm text-gray-300">Tenant dashboard: {{ $tenantDashboardUrl ?? 'Waiting for domain' }}</p>
                </div>
            </div>
            <div class="rounded-xl border border-gray-700 bg-gray-900 p-4 shadow-inner">
                <p class="text-xs uppercase tracking-wide text-gray-400">Notes</p>
                <p class="mt-2 text-sm text-gray-300">Use this space to capture important context about the agency, onboarding progress, and health.</p>
            </div>
            <div id="agency-settings" class="rounded-xl border border-gray-700 bg-gray-900 p-4 shadow-inner">
                <p class="text-xs uppercase tracking-wide text-gray-400">Edit agency</p>
                <form action="{{ route('admin.agencies.update', $agency->id) }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="agency-name" class="text-xs uppercase tracking-wide text-gray-400">Agency Name</label>
                            <input id="agency-name" name="name" value="{{ old('name', $agency->name) }}" required class="mt-2 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-yellow-400 focus:outline-none">
                        </div>
                        <div>
                            <label for="agency-status" class="text-xs uppercase tracking-wide text-gray-400">Status</label>
                            <select id="agency-status" name="status" class="mt-2 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-yellow-400 focus:outline-none">
                                <option value="active" @selected(old('status', $agency->status) === 'active')>Active</option>
                                <option value="suspended" @selected(old('status', $agency->status) === 'suspended')>Suspended</option>
                                <option value="trial" @selected(old('status', $agency->status) === 'trial')>Trial</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="agency-email" class="text-xs uppercase tracking-wide text-gray-400">Contact Email</label>
                            <input id="agency-email" type="email" name="email" value="{{ old('email', $agency->email) }}" class="mt-2 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-yellow-400 focus:outline-none">
                        </div>
                        <div>
                            <label for="agency-phone" class="text-xs uppercase tracking-wide text-gray-400">Phone</label>
                            <input id="agency-phone" name="phone" value="{{ old('phone', $agency->phone) }}" class="mt-2 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-yellow-400 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label for="agency-domain" class="text-xs uppercase tracking-wide text-gray-400">Domain</label>
                        <input id="agency-domain" name="domain" value="{{ old('domain', $agency->domain) }}" placeholder="aktonz.savarix.com" class="mt-2 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-yellow-400 focus:outline-none">
                        <p class="mt-1 text-xs text-gray-400">Enter only the hostname. https:// will be enforced automatically.</p>
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('admin.agencies.index') }}" class="rounded-lg px-4 py-2 text-sm text-gray-300 hover:text-white">Cancel</a>
                        <button type="submit" class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-semibold text-black shadow hover:bg-yellow-300">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
