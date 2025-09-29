<?php

namespace App\Services;

use App\Models\Tenant;
use App\Support\CompanyIdGenerator;
use Database\Seeders\TenantPortalUserSeeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Exceptions\TenantDatabaseAlreadyExistsException;

class TenantProvisioner
{

    /**
     * @param array{subdomain: string, data?: array|null, user?: array|null, name?: string|null, company_name?: string|null} $payload
     */
    public function provision(array $payload): TenantProvisioningResult
    {
        $messages = [];
        $errors = [];
        $subdomain = trim((string) Arr::get($payload, 'subdomain', ''));

        if ($subdomain === '') {
            return TenantProvisioningResult::rolledBack(null, [], ['A subdomain is required for tenant provisioning.']);
        }

        $data = $this->prepareTenantData($payload);

        try {
            $tenant = DB::transaction(function () use ($subdomain, $data, &$messages) {
                $tenant = Tenant::create([
                    'id' => $subdomain,
                ]);

                if (!empty($data)) {
                    $tenant->forceFill($data);
                    $tenant->save();
                }

                $domain = $tenant->domains()->create([
                    'domain' => (string) $this->buildTenantDomain($subdomain),
                ]);

                $messages[] = sprintf('Tenant %s created.', $tenant->getKey());
                $messages[] = sprintf('Domain %s assigned.', $domain->domain);

                return $tenant;
            });
        } catch (Throwable $exception) {
            Log::error('Tenant provisioning failed while creating tenant.', [
                'subdomain' => $subdomain,
                'exception' => $exception,
            ]);

            return TenantProvisioningResult::rolledBack(null, $messages, [
                'Unable to create tenant: ' . $exception->getMessage(),
            ]);
        }

        try {
            $this->createTenantDatabase($tenant);
        } catch (Throwable $exception) {
            Log::error('Tenant provisioning failed while creating tenant database.', [
                'tenant_id' => $tenant->getKey(),
                'exception' => $exception,
            ]);

            $errors[] = 'Tenant database could not be created: ' . $exception->getMessage();
            $this->rollbackTenant($tenant);

            return TenantProvisioningResult::rolledBack(null, $messages, $errors);
        }

        try {
            $this->runTenantMigrations($tenant);
            $messages[] = 'Tenant migrations completed.';
        } catch (Throwable $exception) {
            Log::error('Tenant provisioning failed while running migrations.', [
                'tenant_id' => $tenant->getKey(),
                'exception' => $exception,
            ]);

            $errors[] = 'Tenant migrations failed: ' . $exception->getMessage();
            $this->rollbackTenant($tenant);

            return TenantProvisioningResult::rolledBack(null, $messages, $errors);
        }

        try {
            $this->runTenantSeeds($tenant);
            $messages[] = 'Tenant seeds executed.';
        } catch (Throwable $exception) {
            Log::error('Tenant provisioning failed while seeding tenant.', [
                'tenant_id' => $tenant->getKey(),
                'exception' => $exception,
            ]);

            $errors[] = 'Tenant seeding failed: ' . $exception->getMessage();
            $this->rollbackTenant($tenant);

            return TenantProvisioningResult::rolledBack(null, $messages, $errors);
        }

        $status = TenantProvisioningResult::STATUS_SUCCESS;
        $userPayload = Arr::get($payload, 'user');

        if ($this->hasInitialUserData($userPayload)) {
            try {
                $this->createInitialUser($tenant, $userPayload ?? []);
                $messages[] = 'Initial user created.';
            } catch (Throwable $exception) {
                Log::warning('Initial tenant user could not be created.', [
                    'tenant_id' => $tenant->getKey(),
                    'exception' => $exception,
                ]);

                $errors[] = 'Initial user could not be created: ' . $exception->getMessage();
                $status = TenantProvisioningResult::STATUS_PARTIAL;
            }
        }

        return match ($status) {
            TenantProvisioningResult::STATUS_PARTIAL => TenantProvisioningResult::partial($tenant, $messages, $errors),
            default => TenantProvisioningResult::success($tenant, $messages),
        };
    }

    public function buildTenantDomain(string $subdomain): string
    {
        $subdomain = trim($subdomain);

        if ($subdomain === '') {
            return $this->centralHost();
        }

        $host = $this->centralHost();

        return (string) Str::of($subdomain)->trim('.')->append('.' . $host);
    }

    protected function centralHost(): string
    {
        $appUrl = config('app.url');
        $host = parse_url($appUrl ?: '', PHP_URL_HOST);

        if (is_string($host) && $host !== '') {
            return $host;
        }

        $centralDomains = collect(config('tenancy.central_domains', []));

        $nonIpDomain = $centralDomains->first(function ($domain) {
            return is_string($domain) && !filter_var($domain, FILTER_VALIDATE_IP);
        });

        if (is_string($nonIpDomain) && $nonIpDomain !== '') {
            return $nonIpDomain;
        }

        $first = $centralDomains->first();

        if (is_string($first) && $first !== '') {
            return $first;
        }

        return 'localhost';
    }

    /**
     * @param array{subdomain: string, data?: array|null, user?: array|null, name?: string|null, company_name?: string|null} $payload
     * @return array<string, mixed>
     */
    protected function prepareTenantData(array $payload): array
    {
        $data = Arr::get($payload, 'data', []);
        $data = is_array($data) ? $data : [];

        $companyId = CompanyIdGenerator::generate();
        $data['company_id'] = $companyId;

        $name = Arr::get($payload, 'name');
        if (is_string($name) && $name !== '') {
            $data['name'] = $data['name'] ?? $name;
            $data['company_name'] = $data['company_name'] ?? $name;
        }

        $companyName = Arr::get($payload, 'company_name');
        if (is_string($companyName) && $companyName !== '') {
            $data['company_name'] = $data['company_name'] ?? $companyName;
        }

        return $data;
    }

    protected function runTenantMigrations(Tenant $tenant): void
    {
        $this->runInTenantContext($tenant, function () {
            $connection = config('database.default', 'tenant');
            foreach ($this->tenantMigrationPaths() as $migrationPath) {
                $exitCode = Artisan::call('migrate', [
                    '--database' => $connection,
                    '--force' => true,
                    '--path' => $migrationPath,
                ]);

                if ($exitCode !== 0) {
                    throw new RuntimeException('Tenant migration command failed with exit code ' . $exitCode);
                }
            }
        });
    }

    protected function runTenantSeeds(Tenant $tenant): void
    {
        $this->runInTenantContext($tenant, function () {
            $connection = config('database.default', 'tenant');
            $exitCode = Artisan::call('db:seed', [
                '--class' => TenantPortalUserSeeder::class,
                '--database' => $connection,
                '--force' => true,
            ]);

            if ($exitCode !== 0) {
                throw new RuntimeException('Tenant seeder command failed with exit code ' . $exitCode);
            }
        });
    }

    /**
     * @param callable():void $callback
     */
    protected function runInTenantContext(Tenant $tenant, callable $callback): void
    {
        tenancy()->initialize($tenant);

        try {
            $callback();
        } finally {
            tenancy()->end();
        }
    }

    /**
     * @return array<int, string>
     */
    protected function tenantMigrationPaths(): array
    {
        return [
            database_path('migrations/2014_10_12_000000_create_users_table.php'),
            database_path('migrations/2019_09_15_000010_create_tenants_table.php'),
            database_path('migrations/2019_09_15_000020_create_domains_table.php'),
            database_path('migrations/2025_07_19_000000_add_email_verified_at_to_users_table.php'),
            database_path('migrations/2025_07_29_000001_add_is_admin_to_users_table.php'),
            database_path('migrations/2025_08_01_000001_add_login_token_to_users_table.php'),
        ];
    }

    protected function createTenantDatabase(Tenant $tenant): void
    {
        $databaseManager = app(DatabaseManager::class);

        try {
            $tenant->database()->makeCredentials();
            $databaseManager->ensureTenantCanBeCreated($tenant);
            $tenant->database()->manager()->createDatabase($tenant);
        } catch (TenantDatabaseAlreadyExistsException $exception) {
            Log::info('Tenant database already exists, skipping creation.', [
                'tenant_id' => $tenant->getKey(),
                'exception' => $exception,
            ]);
        }
    }

    /**
     * @param array<string, mixed>|null $userPayload
     */
    protected function hasInitialUserData(?array $userPayload): bool
    {
        if (!is_array($userPayload)) {
            return false;
        }

        return Arr::has($userPayload, ['name', 'email', 'password'])
            && $userPayload['name'] !== null
            && $userPayload['email'] !== null
            && $userPayload['password'] !== null;
    }

    /**
     * @param array{name: string, email: string, password: string} $userPayload
     */
    protected function createInitialUser(Tenant $tenant, array $userPayload): void
    {
        $this->runInTenantContext($tenant, function () use ($userPayload) {
            $userModel = config('auth.providers.users.model');

            if (!is_string($userModel) || $userModel === '') {
                throw new RuntimeException('User model is not configured.');
            }

            $userModel::create([
                'name' => $userPayload['name'],
                'email' => $userPayload['email'],
                'password' => Hash::make($userPayload['password']),
            ]);
        });
    }

    protected function rollbackTenant(Tenant $tenant): void
    {
        try {
            tenancy()->initialize($tenant);
        } catch (Throwable $exception) {
            Log::warning('Failed to initialize tenancy during rollback.', [
                'tenant_id' => $tenant->getKey(),
                'exception' => $exception,
            ]);
        } finally {
            tenancy()->end();
        }

        try {
            $tenant->domains()->delete();
        } catch (Throwable $exception) {
            Log::warning('Failed to delete tenant domains during rollback.', [
                'tenant_id' => $tenant->getKey(),
                'exception' => $exception,
            ]);
        }

        try {
            $tenant->delete();
        } catch (Throwable $exception) {
            Log::warning('Failed to delete tenant during rollback.', [
                'tenant_id' => $tenant->getKey(),
                'exception' => $exception,
            ]);
        }
    }
}
