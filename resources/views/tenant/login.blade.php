<x-guest-layout>
    <div class="max-w-lg mx-auto mt-12">
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-8">
            <h1 class="text-2xl font-semibold text-gray-900 mb-2">Tenant Login</h1>
            <p class="text-sm text-gray-500 mb-6">Sign in to access your tenancy dashboard.</p>

            <form method="POST" action="/login" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        autofocus
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Login
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
