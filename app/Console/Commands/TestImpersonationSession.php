<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TestImpersonationSession extends Command
{
    protected $signature = 'savarix:test-impersonation {agency : Agency ID or slug} {--no-http : Skip HTTP kernel call and only show session details}';

    protected $description = 'Simulate an impersonation session and optionally hit the tenant dashboard with the shared cookie.';

    public function handle(): int
    {
        $identifier = $this->argument('agency');

        $centralConnection = config('tenancy.database.central_connection', config('database.default'));
        DB::setDefaultConnection($centralConnection);

        $agency = Agency::query()
            ->when(is_numeric($identifier), fn ($query) => $query->where('id', (int) $identifier))
            ->when(! is_numeric($identifier), fn ($query) => $query->orWhere('slug', (string) $identifier))
            ->first();

        if (! $agency) {
            $this->error('Agency not found.');

            return self::FAILURE;
        }

        $agencyAdmin = $agency->users()
            ->where('role', 'agency_admin')
            ->orderBy('id')
            ->first();

        if (! $agencyAdmin) {
            $this->error('Agency admin user is missing.');

            return self::FAILURE;
        }

        $this->info('Session configuration');
        $this->line('  Cookie: ' . config('session.cookie'));
        $this->line('  Domain: ' . (config('session.domain') ?? 'not set'));
        $this->line('  SameSite: ' . (config('session.same_site') ?? 'default'));
        $this->line('  Secure: ' . (config('session.secure') ? 'true' : 'false'));

        $ownerId = User::query()->where('role', 'owner')->value('id');

        if ($ownerId) {
            $this->line('  Owner ID: ' . $ownerId);
        } else {
            $this->warn('  Owner ID not found. Impersonator marker will be null.');
        }

        $session = App::make('session.store');
        $session->setName(config('session.cookie'));
        $session->start();

        Auth::shouldUse('web');
        Auth::guard('web')->login($agencyAdmin);

        $session->put([
            'impersonating' => true,
            'impersonator_id' => $ownerId,
            'impersonated_agency_id' => $agency->id,
            'impersonated_user_id' => $agencyAdmin->id,
        ]);

        $session->save();
        $sessionId = $session->getId();

        $this->info('Impersonation session seeded');
        $this->line('  Session ID: ' . $sessionId);
        $this->line('  User: ' . $agencyAdmin->email);

        if ($this->option('no-http')) {
            return self::SUCCESS;
        }

        $dashboardUrl = $agency->tenantDashboardUrl();

        if (! $dashboardUrl) {
            $this->error('Agency domain is missing. Unable to build dashboard URL.');

            return self::FAILURE;
        }

        $this->line('Attempting tenant dashboard request: ' . $dashboardUrl);

        try {
            /** @var HttpKernel $kernel */
            $kernel = App::make(HttpKernel::class);

            $request = Request::create($dashboardUrl, 'GET', server: [
                'HTTP_HOST' => $agency->domain,
            ]);

            $request->cookies->set($session->getName(), $sessionId);

            $response = $kernel->handle($request);
            $status = $response->getStatusCode();

            $this->info('Tenant dashboard status: ' . $status);
            $kernel->terminate($request, $response);

            return $status >= 400 ? self::FAILURE : self::SUCCESS;
        } catch (Throwable $throwable) {
            Log::error('Tenant dashboard session test failed', [
                'agency_id' => $agency->id,
                'error' => $throwable->getMessage(),
            ]);

            $this->error('Dashboard request failed: ' . $throwable->getMessage());

            return self::FAILURE;
        }
    }
}
