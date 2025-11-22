@extends('admin.layouts.app')

@section('breadcrumb')
Agencies
@endsection

@section('content')
<div class="space-y-6" id="create-agency">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white">Agencies</h1>
            <p class="text-sm text-gray-400">Create and manage agency accounts.</p>
        </div>
        <button onclick="openModal('new-agency-modal')" class="inline-flex items-center rounded-lg bg-yellow-400 px-4 py-2 text-sm font-semibold text-black shadow hover:bg-yellow-300">
            + New Agency
        </button>
    </div>

    <div class="rounded-2xl bg-gray-800 p-5 shadow-lg border border-gray-700 space-y-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex-1">
                <input type="text" placeholder="Search agencies by name or email…" class="w-full rounded-md border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100 placeholder:text-gray-500 focus:border-yellow-400 focus:outline-none">
            </div>
            <div class="flex items-center gap-2 text-sm">
                <button class="px-3 py-1 rounded-full bg-gray-900 text-gray-200 border border-gray-700">All</button>
                <button class="px-3 py-1 rounded-full bg-gray-700 text-gray-100 border border-gray-600">Active</button>
                <button class="px-3 py-1 rounded-full bg-gray-900 text-gray-400 border border-gray-800">Suspended</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-gray-200">
                <thead class="text-xs uppercase text-gray-400 border-b border-gray-700">
                    <tr>
                        <th class="py-2 text-left">Agency</th>
                        <th class="py-2 text-left">Primary Email</th>
                        <th class="py-2 text-left">Phone</th>
                        <th class="py-2 text-left">Status</th>
                        <th class="py-2 text-left">Created</th>
                        <th class="py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($agencies as $agency)
                        <tr>
                            <td class="py-3 font-semibold text-white">{{ $agency->name }}</td>
                            <td>{{ $agency->email ?? '—' }}</td>
                            <td>{{ $agency->phone ?? '—' }}</td>
                            <td>
                                <span class="px-2 py-1 rounded-full text-xs {{ $agency->status === 'active' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }}">
                                    {{ ucfirst($agency->status ?? 'active') }}
                                </span>
                            </td>
                            <td>{{ optional($agency->created_at)->format('d M Y') }}</td>
                            <td class="text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('admin.agencies.show', $agency->id) }}" class="underline text-gray-100">View</a>
                                <button class="underline text-gray-400" disabled>Impersonate</button>
                                <button class="underline text-red-400" disabled>Disable</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-sm text-gray-500">No agencies found. Create your first agency to get started.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="new-agency-modal" class="fixed inset-0 hidden items-center justify-center bg-black/60 p-4">
    <div class="w-full max-w-lg rounded-2xl bg-gray-900 p-6 shadow-2xl border border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-400">Add agency</p>
                <h2 class="text-xl font-semibold text-white">Create new agency</h2>
            </div>
            <button onclick="closeModal('new-agency-modal')" class="text-gray-400 hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.agencies.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs uppercase tracking-wide text-gray-400">Agency Name</label>
                <input name="name" required class="mt-2 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-yellow-400 focus:outline-none">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-xs uppercase tracking-wide text-gray-400">Contact Email</label>
                    <input name="email" type="email" class="mt-2 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-yellow-400 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-wide text-gray-400">Phone</label>
                    <input name="phone" class="mt-2 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-yellow-400 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="text-xs uppercase tracking-wide text-gray-400">Domain (optional)</label>
                <input name="domain" placeholder="aktonz.savarix.com" class="mt-2 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-yellow-400 focus:outline-none">
            </div>
            <div>
                <label class="text-xs uppercase tracking-wide text-gray-400">Status</label>
                <select name="status" class="mt-2 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-yellow-400 focus:outline-none">
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input id="send-invite" type="checkbox" checked class="h-4 w-4 rounded border-gray-600 bg-gray-800 text-yellow-400 focus:ring-yellow-400">
                <label for="send-invite" class="text-sm text-gray-200">Create and send invite to admin</label>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('new-agency-modal')" class="rounded-lg px-4 py-2 text-sm text-gray-300 hover:text-white">Cancel</button>
                <button type="submit" class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-semibold text-black shadow hover:bg-yellow-300">Create Agency</button>
            </div>
        </form>
    </div>
</div>
@endsection
