<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\GenerateMonthlyInvoices;
use App\Models\Invoice;
use App\Models\Property;
use App\Jobs\SyncPropertyToPortals;
use App\Notifications\RentDueNotification;
use Illuminate\Support\Facades\Notification;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\AssignCompanyIds::class,
        \App\Console\Commands\GenerateLoginToken::class,
        \App\Console\Commands\CreateUserWithLoginToken::class,
        \App\Console\Commands\FixAktonzTenantEmail::class,
        \App\Console\Commands\CreateAktonzTenant::class,
        \App\Console\Commands\FixAktonzTenantData::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new GenerateMonthlyInvoices())->daily();

        $schedule->call(function () {
            Property::where('publish_to_portal', true)->each(function (Property $property) {
                SyncPropertyToPortals::dispatch($property);
            });
        })->daily();

        $schedule->call(function () {
            Invoice::where('status', 'unpaid')
                ->whereDate('due_date', now()->toDateString())
                ->with('tenancy.contact')
                ->get()
                ->each(function ($invoice) {
                    $email = $invoice->tenancy->contact?->email;
                    if ($email) {
                        Notification::route('mail', $email)
                            ->notify(new RentDueNotification($invoice));
                    }
                });
        })->daily();
    }

    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
