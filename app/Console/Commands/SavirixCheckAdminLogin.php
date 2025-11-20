<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SavirixCheckAdminLogin extends Command
{
    protected $signature = 'savarix:check-admin-login';

    protected $description = 'Check owner admin secret path and route registration.';

    public function handle(): int
    {
        $defaultGuard = config('auth.defaults.guard');
        $adminProvider = config("auth.guards.{$defaultGuard}.provider");
        $providerConfig = config("auth.providers.{$adminProvider}");
        $centralConnection = config('tenancy.database.central_connection');
        $databaseDefault = config('database.default');

        $appUrl = config('app.url') ?? env('APP_URL');
        $adminPath = env('SAVARIX_ADMIN_PATH', 'savarix-admin');
        $centralDomains = config('tenancy.central_domains') ?? env('TENANCY_CENTRAL_DOMAINS');

        $this->info('APP_URL: ' . ($appUrl ?? ''));
        $this->info('SAVARIX_ADMIN_PATH: ' . ($adminPath ?: 'savarix-admin'));
        $this->info("Auth default guard: {$defaultGuard}");
        $this->info("Auth provider for {$defaultGuard}: {$adminProvider}");
        $this->info('Auth provider driver: ' . ($providerConfig['driver'] ?? ''));    
        $this->info('Auth provider model/table: ' . ($providerConfig['model'] ?? $providerConfig['table'] ?? 'unknown'));
        $this->info("database.default: {$databaseDefault}");
        $this->info("tenancy.database.central_connection: {$centralConnection}");
        $this->line('--- central DB credentials (env) ---');
        $this->line('DB_CONNECTION=' . env('DB_CONNECTION'));
        $this->line('DB_DATABASE=' . env('DB_DATABASE'));
        $this->line('DB_USERNAME=' . env('DB_USERNAME'));

        $this->info('--- Central DB connectivity check ---');
        $this->checkCentralDatabase($centralConnection);

        $this->info('--- Central admin users (role owner or is_admin=1) ---');
        $this->displayAdminUsers();

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

    protected function checkCentralDatabase(string $centralConnection): void
    {
        $connectionName = $centralConnection ?: config('database.default');

        try {
            $connection = DB::connection($connectionName);
            $connection->select('select 1');

            $this->info("Central DB connection '{$connectionName}' OK (select 1).");
            $this->line('Database name: ' . $connection->getDatabaseName());
        } catch (Throwable $e) {
            $this->error("Central DB connection failed for '{$connectionName}': " . $e->getMessage());
            $this->warn("If you see SQLSTATE[HY000] [1045] for user '" . env('DB_USERNAME') . "', fix MySQL privileges and ensure the password in .env matches.");

            Log::error('savarix:check-admin-login DB connectivity failure', [
                'connection' => $connectionName,
                'message' => $e->getMessage(),
            ]);
        }
    }

    protected function displayAdminUsers(): void
    {
        try {
            $admins = User::query()
                ->where(fn ($query) => $query->where('role', 'owner')->orWhere('is_admin', true))
                ->select(['id', 'name', 'email', 'password'])
                ->get()
                ->map(function (User $user) {
                    $hashInfo = $this->describePasswordHash($user->password);

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'connection' => $user->getConnectionName() ?? config('database.default'),
                        'has_password_hash' => $hashInfo['valid'] ? 'yes' : 'no',
                        'hash_algo' => $hashInfo['algo'],
                    ];
                });

            if ($admins->isEmpty()) {
                $this->warn('No admin/owner users found in the central users table.');

                return;
            }

            $this->table(['id', 'name', 'email', 'connection', 'has_password_hash', 'hash_algo'], $admins->toArray());
        } catch (Throwable $e) {
            $this->error('Failed to query admin users: ' . $e->getMessage());
        }
    }

    /**
     * @return array{valid: bool, algo: string}
     */
    protected function describePasswordHash(?string $password): array
    {
        if (! $password) {
            return ['valid' => false, 'algo' => 'missing'];
        }

        $info = password_get_info($password);
        $isValid = ($info['algo'] ?? 0) !== 0 || Str::startsWith($password, '$2y$');
        $algo = $info['algoName'] ?? 'unknown';

        if (! $isValid && Hash::needsRehash($password) === false) {
            $isValid = true;
        }

        return ['valid' => $isValid, 'algo' => $algo];
    }
}
