@extends('admin.layouts.app')

@section('breadcrumb')
Agencies / {{ $agency->name }} / Users
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white">Users in {{ $agency->name }}</h1>
            <p class="text-sm text-gray-400">Manage access for staff at {{ $agency->name }}.</p>
        </div>
        <button onclick="openModal('invite-user-modal')" class="inline-flex items-center rounded-lg bg-yellow-400 px-4 py-2 text-sm font-semibold text-black shadow hover:bg-yellow-300">Invite User</button>
    </div>

    <div class="rounded-2xl bg-gray-800 p-5 shadow-lg border border-gray-700 space-y-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-2 text-sm">
                <button type="button" class="px-3 py-1 rounded-full bg-gray-900 text-gray-200 border border-gray-700">All</button>
                <button type="button" class="px-3 py-1 rounded-full bg-gray-700 text-gray-100 border border-gray-600">Admins</button>
                <button type="button" class="px-3 py-1 rounded-full bg-gray-900 text-gray-400 border border-gray-800">Negotiators</button>
                <button type="button" class="px-3 py-1 rounded-full bg-gray-900 text-gray-400 border border-gray-800">Property Managers</button>
            </div>
            <div class="flex-1 lg:max-w-xs">
                <input type="text" placeholder="Search users…" class="w-full rounded-md border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100 placeholder:text-gray-500 focus:border-yellow-400 focus:outline-none">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-gray-200">
                <thead class="text-xs uppercase text-gray-400 border-b border-gray-700">
                    <tr>
                        <th class="py-2 text-left">Name</th>
                        <th class="py-2 text-left">Email</th>
                        <th class="py-2 text-left">Role</th>
                        <th class="py-2 text-left">Status</th>
                        <th class="py-2 text-left">Last Login</th>
                        <th class="py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($users as $user)
                        <tr>
                            <td class="py-3 font-semibold text-white">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="rounded-full bg-gray-900 px-2 py-1 text-xs font-semibold text-gray-100 border border-gray-700">{{ ucwords(str_replace('_', ' ', $user->role)) }}</span>
                            </td>
                            <td>
                                <span class="px-2 py-1 rounded-full text-xs bg-green-500/10 text-green-400">Active</span>
                            </td>
                            <td>{{ optional($user->updated_at)->diffForHumans() ?? '—' }}</td>
                            <td class="text-right space-x-2 whitespace-nowrap">
                                <button class="underline text-gray-100" disabled>Edit</button>
                                <button class="underline text-gray-100" disabled>Reset PW</button>
                                <form method="POST" action="{{ route('admin.agencies.users.destroy', [$agency->id, $user->id]) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="underline text-red-400">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-sm text-gray-500">No users yet – invite your first agency admin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="invite-user-modal" class="fixed inset-0 hidden items-center justify-center bg-black/60 p-4">
    <div class="w-full max-w-lg rounded-2xl bg-gray-900 p-6 shadow-2xl border border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-400">Invite user</p>
                <h2 class="text-xl font-semibold text-white">Add a new user</h2>
            </div>
            <button type="button" onclick="closeModal('invite-user-modal')" class="text-gray-400 hover:text-white" aria-label="Close invite user modal">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.agencies.users.store', $agency->id) }}" class="space-y-4">
            @csrf
            <div>
                <label for="invite-name" class="text-xs uppercase tracking-wide text-gray-400">Name</label>
                <input id="invite-name" name="name" placeholder="Enter the user's full name" required class="mt-2 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-yellow-400 focus:outline-none">
            </div>
            <div>
                <label for="invite-email" class="text-xs uppercase tracking-wide text-gray-400">Email</label>
                <input id="invite-email" name="email" type="email" placeholder="name@example.com" required class="mt-2 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-yellow-400 focus:outline-none">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="invite-role" class="text-xs uppercase tracking-wide text-gray-400">Role</label>
                    <select id="invite-role" name="role" class="mt-2 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-yellow-400 focus:outline-none">
                        <option value="agency_admin">Agency Admin</option>
                        <option value="agent">Negotiator</option>
                        <option value="property_manager">Property Manager</option>
                        <option value="viewer">Viewer</option>
                    </select>
                </div>
                <div>
                    <label for="invite-password" class="text-xs uppercase tracking-wide text-gray-400">Temporary Password</label>
                    <input id="invite-password" name="password" placeholder="Create a temporary password" required class="mt-2 w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-100 focus:border-yellow-400 focus:outline-none">
                </div>
            </div>
            <div class="space-y-2">
                <p class="text-xs uppercase tracking-wide text-gray-400">Delivery</p>
                <div class="flex items-center gap-2">
                    <input id="send-welcome" type="checkbox" class="h-4 w-4 rounded border-gray-600 bg-gray-800 text-yellow-400 focus:ring-yellow-400" checked>
                    <label for="send-welcome" class="text-sm text-gray-200">Send welcome email with login link</label>
                </div>
                <div class="flex items-center gap-2">
                    <input id="require-reset" type="checkbox" class="h-4 w-4 rounded border-gray-600 bg-gray-800 text-yellow-400 focus:ring-yellow-400">
                    <label for="require-reset" class="text-sm text-gray-200">Require password reset on first login</label>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('invite-user-modal')" class="rounded-lg px-4 py-2 text-sm text-gray-300 hover:text-white">Cancel</button>
                <button type="submit" class="rounded-lg bg-yellow-400 px-4 py-2 text-sm font-semibold text-black shadow hover:bg-yellow-300">Send invite</button>
            </div>
        </form>
    </div>
</div>
@endsection
