<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} — Agency</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @php($hasManifest = file_exists(public_path('build/manifest.json')))
    @if ($hasManifest)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script src="{{ asset('js/app.js') }}" defer></script>
    @endif

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    @php($user = Auth::user())
    @php($tenantName = tenant('name') ?? config('app.name', 'Agency'))

    <div class="min-h-screen flex">
        <aside class="w-64 bg-white border-r shadow-sm flex flex-col">
            <div class="px-6 py-4 border-b">
                <div class="text-xl font-bold text-indigo-600">{{ $tenantName }}</div>
                <div class="text-sm text-gray-500">Agency Dashboard</div>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a class="flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-700" href="#">
                    <span class="mr-3">📊</span> Overview
                </a>
                <a class="flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-700" href="#">
                    <span class="mr-3">🏠</span> Listings
                </a>
                <a class="flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-700" href="#">
                    <span class="mr-3">📅</span> Viewings
                </a>
                <a class="flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-700" href="#">
                    <span class="mr-3">👥</span> Clients
                </a>
                <a class="flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-700" href="#">
                    <span class="mr-3">🧾</span> Offers & Deals
                </a>
            </nav>
            <div class="px-6 py-4 border-t">
                <div class="text-sm font-semibold text-gray-700">Signed in</div>
                <div class="text-sm text-gray-500">{{ optional($user)->name }}</div>
                <div class="text-xs text-gray-400">{{ optional($user)->email }}</div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col">
            <header class="bg-white border-b shadow-sm">
                <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ $tenantName }}</p>
                        <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="hidden sm:block">
                            <input type="search" placeholder="Search properties, clients..." class="w-64 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700">{{ now()->format('M d, Y') }}</span>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-6">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>

            <footer class="bg-white border-t">
                <div class="max-w-7xl mx-auto px-6 py-4 text-sm text-gray-500 flex justify-between items-center">
                    <span>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</span>
                    <span class="text-gray-400">Tenant: {{ $tenantName }}</span>
                </div>
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
