<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class SavirixCheckAdminLogin extends Command
{
    protected $signature = 'savarix:check-admin-login';

    protected $description = 'Check owner admin secret path and route registration.';

    public function handle(): int
    {
        $appUrl = config('app.url') ?? env('APP_URL');
        $adminPath = env('SAVARIX_ADMIN_PATH', 'savarix-admin');
        $centralDomains = config('tenancy.central_domains') ?? env('TENANCY_CENTRAL_DOMAINS');

        $this->info('APP_URL: ' . ($appUrl ?? ''));
        $this->info('SAVARIX_ADMIN_PATH: ' . ($adminPath ?: 'savarix-admin'));

        if (is_array($centralDomains)) {
            $domainsDisplay = implode(', ', $centralDomains);
        } else {
            $domainsDisplay = (string) $centralDomains;
        }

        $this->info('TENANCY_CENTRAL_DOMAINS: ' . $domainsDisplay);

        $this->info('--- admin.login routes ---');

        Artisan::call('route:list', ['--name' => 'admin.login']);

        $routeOutput = trim(Artisan::output());

        if ($routeOutput === '') {
            $this->warn('No routes matched admin.login.');
        } else {
            $this->line($routeOutput);
        }

        $hasRoute = Str::contains($routeOutput, 'admin.login');

        $baseUrl = rtrim((string) ($appUrl ?? ''), '/');
        $secretPath = ltrim($adminPath ?: 'savarix-admin', '/');
        $loginUrl = $baseUrl . '/' . $secretPath . '/login';

        $this->info('--- Summary ---');
        $this->info('Try this URL: ' . $loginUrl);

        if ($hasRoute) {
            $this->info('admin.login route found.');
        } else {
            $this->warn('admin.login route not found.');
        }

        return self::SUCCESS;
    }
}
