<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4">
        <div class="max-w-4xl w-full grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Left: Form --}}
            <div class="bg-white shadow-lg rounded-xl p-8">
                <div class="mb-6 text-center">
                    <a href="{{ url('/') }}" class="inline-flex items-center justify-center">
                        <x-application-logo class="w-12 h-12 text-indigo-600" />
                    </a>
                    <h1 class="mt-4 text-2xl font-semibold text-gray-900">
                        Create your Savarix agency account
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        For UK estate &amp; lettings agents.
                    </p>
                </div>

                <form method="POST" action="{{ route('agency.register') }}" class="space-y-4">
                    @csrf

                    {{-- Agency name --}}
                    <div>
                        <x-label for="agency_name" value="Agency name" />
                        <x-input id="agency_name" name="agency_name" type="text" class="mt-1 block w-full"
                                 value="{{ old('agency_name') }}" required autofocus />
                        <x-input-error for="agency_name" class="mt-2" />
                    </div>

                    {{-- URL preview (read only) --}}
                    <div>
                        <x-label value="Your Savarix URL" />
                        <div class="mt-1 flex items-center text-sm text-gray-500">
                            <span>
                                This will be based on your agency name, for example:
                                <span class="font-mono font-semibold">londoncapitalinvestments.savarix.com</span>
                            </span>
                        </div>
                    </div>

                    {{-- Agency size --}}
                    <div>
                        <x-label for="agency_size" value="Agency size" />
                        <select id="agency_size" name="agency_size"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select...</option>
                            <option value="1-3" @selected(old('agency_size') === '1-3')>1–3 people</option>
                            <option value="4-10" @selected(old('agency_size') === '4-10')>4–10 people</option>
                            <option value="11-25" @selected(old('agency_size') === '11-25')>11–25 people</option>
                            <option value="26+" @selected(old('agency_size') === '26+')>26+ people</option>
                        </select>
                        <x-input-error for="agency_size" class="mt-2" />
                    </div>

                    {{-- Primary agent name --}}
                    <div>
                        <x-label for="name" value="Your name" />
                        <x-input id="name" name="name" type="text" class="mt-1 block w-full"
                                 value="{{ old('name') }}" required />
                        <x-input-error for="name" class="mt-2" />
                    </div>

                    {{-- Work email --}}
                    <div>
                        <x-label for="email" value="Work email" />
                        <x-input id="email" name="email" type="email" class="mt-1 block w-full"
                                 value="{{ old('email') }}" required />
                        <x-input-error for="email" class="mt-2" />
                    </div>

                    {{-- Mobile --}}
                    <div>
                        <x-label for="phone" value="Mobile number" />
                        <x-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                 value="{{ old('phone') }}" />
                        <x-input-error for="phone" class="mt-2" />
                    </div>

                    {{-- Office postcode --}}
                    <div>
                        <x-label for="postcode" value="Office postcode" />
                        <x-input id="postcode" name="postcode" type="text" class="mt-1 block w-full"
                                 value="{{ old('postcode') }}" />
                        <x-input-error for="postcode" class="mt-2" />
                    </div>

                    {{-- Password --}}
                    <div>
                        <x-label for="password" value="Password" />
                        <x-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                        <x-input-error for="password" class="mt-2" />
                    </div>

                    {{-- Confirm password --}}
                    <div>
                        <x-label for="password_confirmation" value="Confirm password" />
                        <x-input id="password_confirmation" name="password_confirmation" type="password"
                                 class="mt-1 block w-full" required />
                    </div>

                    {{-- CTA --}}
                    <div class="pt-4">
                        <x-button class="w-full justify-center">
                            Create my agency account
                        </x-button>
                    </div>

                    <div class="mt-4 text-center text-sm text-gray-600">
                        Already using Savarix?
                        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                            Login here
                        </a>
                    </div>
                </form>
            </div>

            {{-- Right: Product highlights --}}
            <div class="hidden md:flex flex-col justify-center">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    Built for UK estate &amp; lettings agents
                </h2>
                <ul class="space-y-3 text-sm text-gray-700">
                    <li>• Centralise applicants, landlords &amp; properties in one CRM.</li>
                    <li>• Smart matching between applicant requirements and listings.</li>
                    <li>• Track viewings, offers and tenancies from first enquiry to move-in.</li>
                    <li>• Store photos, inventories and documents securely in the cloud.</li>
                    <li>• UK-first onboarding – tailored for lettings regulations.</li>
                </ul>
                <p class="mt-6 text-xs text-gray-500">
                    By creating an account you agree to the Savarix Terms and Privacy Policy.
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
