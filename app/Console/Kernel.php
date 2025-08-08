<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\GenerateMonthlyInvoices;
use App\Models\Invoice;
use App\Notifications\RentDueNotification;
use Illuminate\Support\Facades\Notification;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\AssignCompanyIds::class,
        \App\Console\Commands\GenerateLoginToken::class,
        \App\Console\Commands\CreateUserWithLoginToken::class,
        \App\Console\Commands\FixAktonzTenantEmail::class,
        \App\Console\Commands\CreateAktonzTenant::class,
        \App\Console\Commands\FixAktonzTenantData::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new GenerateMonthlyInvoices())->daily();

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

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
