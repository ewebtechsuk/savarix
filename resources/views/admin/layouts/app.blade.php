<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savarix Owner Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #111827; }
        .sidebar-collapsed .sidebar-label { display: none; }
        .sidebar-collapsed .sidebar-logo { justify-content: center; }
    </style>
</head>
<body class="min-h-screen text-gray-100">
    @php($adminUser = auth()->user())
    <div id="app" class="flex min-h-screen">
        <aside id="sidebar" class="w-64 bg-gray-950 border-r border-gray-800 flex-shrink-0 transition-all duration-200">
            <div class="flex items-center justify-between px-4 py-5 border-b border-gray-800 sidebar-logo">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-yellow-400 text-black font-extrabold">S</div>
                    <div class="sidebar-label">
                        <p class="text-sm font-semibold text-gray-100">Savarix</p>
                        <p class="text-xs text-gray-400">Owner Admin</p>
                    </div>
                </div>
                <button type="button" onclick="toggleSidebar()" class="p-2 rounded-lg bg-gray-900 text-gray-300 hover:text-white" aria-label="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6 6 10.5m0 0L10.5 15M6 10.5H21" />
                    </svg>
                </button>
            </div>

            <nav class="px-3 py-4 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-900 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300' }}">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-900/60 text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75 12 4.5l9 5.25M3 9.75V18a.75.75 0 0 0 .75.75H9.75M3 9.75l9 5.25m0 0 9-5.25m-9 5.25V21m0-6.75 6.75-3.937M9.75 18H21.75a.75.75 0 0 0 .75-.75V9.75" />
                        </svg>
                    </span>
                    <span class="sidebar-label">Dashboard</span>
                </a>
                <a href="{{ route('admin.agencies.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-900 {{ request()->routeIs('admin.agencies.*') ? 'bg-gray-900 text-white' : 'text-gray-300' }}">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-900/60 text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 9.75 12 4.5l7.5 5.25v6.75a1.5 1.5 0 0 1-1.5 1.5h-12a1.5 1.5 0 0 1-1.5-1.5V9.75Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 21v-6.75h4.5V21" />
                        </svg>
                    </span>
                    <span class="sidebar-label">Agencies</span>
                </a>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-500 cursor-not-allowed">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-900/40 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c-1.243 0-2.25-.896-2.25-2s1.007-2 2.25-2 2.25.896 2.25 2-1.007 2-2.25 2Zm0 0c-3.351 0-6 2.58-6 5.76 0 1.6.704 3.05 1.85 4.14.383.368.6.882.6 1.416v.184c0 .747.522 1.375 1.231 1.52 1.075.223 2.189.33 3.319.33 1.13 0 2.244-.107 3.319-.33.709-.145 1.231-.773 1.231-1.52v-.184c0-.534.217-1.048.6-1.416 1.146-1.09 1.85-2.54 1.85-4.14 0-3.18-2.649-5.76-6-5.76Z" />
                        </svg>
                    </span>
                    <span class="sidebar-label">Agency Users</span>
                </a>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-500 cursor-not-allowed">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-900/40 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25m0 0h-3m3 0h3m-3 0V3m0 2.25v3M12 6.75H6.75A2.25 2.25 0 0 0 4.5 9v9.75A2.25 2.25 0 0 0 6.75 21h10.5A2.25 2.25 0 0 0 19.5 18.75V12" />
                        </svg>
                    </span>
                    <span class="sidebar-label">System Health</span>
                </a>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-500 cursor-not-allowed">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-900/40 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75V12m0 0V14.25M9.75 12h4.5M12 6.75V9.75M12 14.25V17.25M6.75 7.5a2.25 2.25 0 0 0-2.25 2.25v6.75A2.25 2.25 0 0 0 6.75 18.75h10.5A2.25 2.25 0 0 0 19.5 16.5V9.75A2.25 2.25 0 0 0 17.25 7.5H6.75Z" />
                        </svg>
                    </span>
                    <span class="sidebar-label">Settings</span>
                </a>
            </nav>
        </aside>

        <div class="flex flex-1 flex-col">
            <header class="flex items-center justify-between px-6 py-4 border-b border-gray-800 bg-gray-900/80 backdrop-blur">
                <div class="flex items-center gap-2 text-sm text-gray-400">
                    <span class="text-gray-300 font-semibold">Dashboard</span>
                    @php($breadcrumb = trim($__env->yieldContent('breadcrumb')))
                    @if($breadcrumb)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7" />
                        </svg>
                        <span class="text-gray-400">{{ $breadcrumb }}</span>
                    @endif
                </div>
                <div class="relative">
                    <button type="button" id="user-menu-button" onclick="toggleUserMenu()" class="flex items-center gap-3 rounded-lg bg-gray-800 px-3 py-2 text-sm hover:bg-gray-700" aria-label="Toggle user menu">
                        <div class="text-right hidden sm:block">
                            <p class="font-semibold text-gray-100">{{ $adminUser?->name ?? 'Admin User' }}</p>
                            <p class="text-xs text-gray-400">{{ $adminUser?->email ?? 'admin@savarix.com' }}</p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-gray-700 flex items-center justify-center font-semibold">
                            {{ strtoupper(substr($adminUser?->name ?? 'SA', 0, 2)) }}
                        </div>
                    </button>
                    <div id="user-menu" class="absolute right-0 mt-2 w-48 rounded-lg bg-gray-800 border border-gray-700 shadow-xl hidden">
                        <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Menu</div>
                        <div class="border-t border-gray-700"></div>
                        <button type="button" class="w-full text-left px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 disabled:text-gray-500" aria-label="Switch to agency (disabled)" disabled>Switch to Agency</button>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-700">My Profile</a>
                        <form method="POST" action="{{ route('admin.logout') }}" class="border-t border-gray-700">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-300 hover:bg-red-500/10">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-6 bg-gray-900">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            document.body.classList.toggle('sidebar-collapsed');
            sidebar.classList.toggle('w-64');
            sidebar.classList.toggle('w-20');
        }

        function toggleUserMenu() {
            const menu = document.getElementById('user-menu');
            menu.classList.toggle('hidden');
        }

        document.addEventListener('click', function(event) {
            const menu = document.getElementById('user-menu');
            const button = document.getElementById('user-menu-button');
            if (!menu || !button) return;
            if (!menu.classList.contains('hidden') && !menu.contains(event.target) && !button.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });

        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
