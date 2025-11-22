<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Savarix – Agent' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen">
<div class="min-h-screen flex">
    {{-- Sidebar --}}
    <aside class="w-64 bg-slate-900 text-slate-100 flex flex-col">
        <div class="px-4 py-4 text-lg font-semibold border-b border-slate-800">
            Savarix<span class="text-amber-400">Agent</span>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
            <a href="{{ route('agent.dashboard') }}"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('agent.dashboard')) bg-slate-800 @endif">
                <span>Dashboard</span>
            </a>
            <a href="{{ route('agent.properties.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('agent.properties.*')) bg-slate-800 @endif">
                <span>Properties</span>
            </a>
            <a href="{{ route('agent.contacts.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('agent.contacts.*')) bg-slate-800 @endif">
                <span>Contacts</span>
            </a>
            <a href="{{ route('agent.tasks.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('agent.tasks.*')) bg-slate-800 @endif">
                <span>Diary & Tasks</span>
            </a>
            <a href="{{ route('agent.documents.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-slate-800 @if(request()->routeIs('agent.documents.*')) bg-slate-800 @endif">
                <span>Documents</span>
            </a>
        </nav>

        <div class="px-4 py-4 border-t border-slate-800 text-xs text-slate-400">
            <div>{{ auth()->user()->name ?? 'Agent' }}</div>
            <form method="POST" action="{{ route('tenant.logout') }}" class="mt-2">
                @csrf
                <button class="text-amber-400 hover:text-amber-300">Log out</button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col">
        {{-- Top bar --}}
        <header class="h-14 bg-white border-b border-slate-200 flex items-center justify-between px-6">
            <div class="flex items-center gap-4">
                <h1 class="text-lg font-semibold">
                    {{ $title ?? 'Dashboard' }}
                </h1>
            </div>
            <div class="flex items-center gap-4">
                {{-- Placeholder for global search / branch selector --}}
                <input type="search" placeholder="Search properties, contacts..."
                       class="text-sm rounded-md border border-slate-300 px-3 py-1.5 w-64">
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 p-6">
            @if (session('status'))
                <div class="mb-4 rounded border border-emerald-400 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
