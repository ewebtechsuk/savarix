<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Sentry\State\Scope;

class SentryTenantContextServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! app()->bound('sentry')) {
            return;
        }

        app('sentry')->configureScope(function (Scope $scope) {
            $host = request()->getHost() ?? null;
            $tenant = function_exists('tenancy') && tenancy()->initialized
                ? tenancy()->tenant?->getTenantKey()
                : null;

            $user = auth()->user();
            $role = $user?->role ?? null;

            if (! empty($host)) {
                $scope->setTag('host', $host);
            }

            if (! is_null($tenant)) {
                $scope->setTag('tenant', (string) $tenant);
            }

            if (! is_null($role)) {
                $scope->setTag('role', (string) $role);
            }

            if ($tenant) {
                $scope->setContext('tenant', [
                    'id' => $tenant,
                    'domain' => $host,
                ]);
            }
        });
    }
}
