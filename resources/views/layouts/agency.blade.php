<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ tenant('name') ?? config('app.name', 'Savarix') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @php
        // Tenancy serves dashboards from tenant domains; use Vite when the manifest is
        // available and fall back to the compiled public assets to guarantee styling.
        $viteManifestExists = file_exists(public_path('build/manifest.json'));
    @endphp

    @if ($viteManifestExists)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script src="{{ asset('js/app.js') }}" defer></script>
    @endif

    @stack('styles')
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen flex">
        <aside class="w-64 bg-white border-r hidden md:block">
            <div class="p-6 border-b">
                <div class="text-xl font-semibold text-gray-900">{{ tenant('name') ?? config('app.name') }}</div>
                <p class="text-sm text-gray-500 mt-1">Agency dashboard</p>
            </div>
            <nav class="p-4 space-y-1">
                @php
                    $navItems = [
                        ['label' => 'Dashboard', 'icon' => 'home', 'route' => '#'],
                        ['label' => 'Properties', 'icon' => 'office-building', 'route' => '#'],
                        ['label' => 'Viewings', 'icon' => 'calendar', 'route' => '#'],
                        ['label' => 'Clients', 'icon' => 'user-group', 'route' => '#'],
                        ['label' => 'Reports', 'icon' => 'chart-bar', 'route' => '#'],
                        ['label' => 'Settings', 'icon' => 'cog', 'route' => '#'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    <a href="{{ $item['route'] }}" class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100">
                        <span class="material-icons-outlined text-gray-400 mr-3 text-lg">{{ $item['icon'] }}</span>
                        <span class="font-medium">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white border-b">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-sm text-gray-500">Welcome back</p>
                        <h1 class="text-2xl font-semibold text-gray-900">{{ tenant('name') ?? 'Your Agency' }}</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="hidden md:block text-right">
                            <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name ?? 'User' }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->email ?? 'user@example.com' }}</p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-semibold">
                            {{ Str::of(Auth::user()->name ?? 'U')->trim()->explode(' ')->map(fn ($part) => Str::substr($part, 0, 1))->implode('') }}
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    @yield('content')
                </div>
            </main>

            <footer class="bg-white border-t">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-center text-sm text-gray-500">
                    &copy; {{ date('Y') }} {{ tenant('name') ?? config('app.name') }}. All rights reserved.
                </div>
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
