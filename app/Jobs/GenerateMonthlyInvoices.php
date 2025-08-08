<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Tenancy;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateMonthlyInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $monthStart = Carbon::today()->startOfMonth();

        Tenancy::where('status', 'active')->each(function (Tenancy $tenancy) use ($monthStart) {
            $exists = Invoice::where('tenancy_id', $tenancy->id)
                ->whereDate('date', $monthStart)
                ->exists();

            if (! $exists) {
                Invoice::create([
                    'number' => 'INV-' . $monthStart->format('Ym') . '-' . $tenancy->id,
                    'date' => $monthStart,
                    'contact_id' => $tenancy->contact_id,
                    'property_id' => $tenancy->property_id,
                    'tenancy_id' => $tenancy->id,
                    'amount' => $tenancy->rent,
                    'due_date' => $monthStart->copy()->addMonth()->startOfMonth(),
                    'status' => 'unpaid',
                ]);
            }
        });
    }
}
