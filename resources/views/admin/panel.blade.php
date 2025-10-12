@extends('layouts.app')

@section('content')
<div class="space-y-10">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="text-sm text-slate-500" aria-label="Breadcrumb">
                <ol class="flex items-center gap-2">
                    <li class="flex items-center gap-2">
                        <span class="font-medium text-slate-400">Admin Panel</span>
                        <svg class="h-3 w-3 text-slate-300" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 2L8 6L4 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </li>
                    <li class="font-semibold text-slate-600">Admin Panel</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-semibold text-slate-900">Admin Panel</h1>
            <p class="text-sm text-slate-500">Overview of your tenant subscription, billing and settings.</p>
        </div>
        <div class="flex flex-col text-sm text-right text-slate-500">
            <span class="font-semibold text-slate-700">Shah Chowdhury</span>
            <span>Admin User</span>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-4">
        <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">User Subscriptions</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">1 subscription</p>
            </div>
            <div class="flex-1 space-y-6 px-6 py-6">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Manage</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Alerts</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Auctions</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Blocked Numbers</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Chain Checks</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Clients</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Companies</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Documents</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Emails</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Sales Progression</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Task Management</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Users</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">User Logs</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>Last Login</span><span>Today</span></li>
                        <li class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>User Region</span><span>United Kingdom</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Next Billing Date</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">24/10/2025</p>
            </div>
            <div class="flex-1 space-y-6 px-6 py-6">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">EIC Checks</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Client Onboarding Checks</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Chain Checks</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Pre-Sale Checks</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Pre-Tenancy Checks</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Registered Numbers</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Custom Certificates</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Billing</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>Calls &amp; SMS Usage</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>Invoices</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>Payment Methods</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Next Amount Due (inc. VAT)</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">£39.60</p>
            </div>
            <div class="flex-1 space-y-6 px-6 py-6">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Account</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-purple-500"></span>Log</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-purple-500"></span>Settings</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-purple-500"></span>Billing</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-purple-500"></span>Calls &amp; SMS Usage</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-purple-500"></span>Invoices</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-purple-500"></span>Payment Methods</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sales</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>Products</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>Promotions</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>Usage</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Stripe Name &amp; Email</p>
                <div class="mt-3 space-y-1">
                    <p class="text-base font-semibold text-slate-900">Shah Chowdhury</p>
                    <p class="text-sm text-slate-500">shah@example.com</p>
                </div>
            </div>
            <div class="flex-1 space-y-6 px-6 py-6">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">API Keys</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>Integrations</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>Marketing</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>Webhooks</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Security</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Data Tools</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>GDPR Data Imports</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Merge Contacts</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
