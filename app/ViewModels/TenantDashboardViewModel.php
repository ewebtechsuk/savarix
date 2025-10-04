<?php

namespace App\ViewModels;

use App\Models\ContactCommunication;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Tenancy;
use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TenantDashboardViewModel
{
    public function __construct(
        protected Tenant $tenant,
        protected Collection $tenancies,
        protected Collection $payments,
        protected Collection $maintenanceRequests,
        protected Collection $communications,
    ) {
    }

    public static function fromTenant(Tenant $tenant): self
    {
        $tenancies = Tenancy::query()
            ->with(['property', 'contact'])
            ->whereHas('property', function ($query) use ($tenant) {
                $query->where('tenant_id', $tenant->getKey());
            })
            ->orderByDesc('start_date')
            ->get();

        $payments = Payment::query()
            ->with(['tenancy.property', 'tenancy.contact'])
            ->where('status', 'pending')
            ->whereHas('tenancy', function ($query) use ($tenant) {
                $query->whereHas('property', function ($propertyQuery) use ($tenant) {
                    $propertyQuery->where('tenant_id', $tenant->getKey());
                });
            })
            ->orderBy('created_at')
            ->take(5)
            ->get();

        $maintenanceRequests = MaintenanceRequest::query()
            ->with('property')
            ->where('tenant_id', $tenant->getKey())
            ->whereNotIn('status', ['resolved', 'closed', 'completed'])
            ->orderByDesc('created_at')
            ->get();

        $contactIds = $tenancies
            ->pluck('contact_id')
            ->filter()
            ->unique();

        $communications = ContactCommunication::query()
            ->with('contact')
            ->when($contactIds->isNotEmpty(), function ($query) use ($contactIds) {
                $query->whereIn('contact_id', $contactIds);
            })
            ->latest()
            ->take(5)
            ->get();

        return new self(
            tenant: $tenant,
            tenancies: $tenancies,
            payments: $payments,
            maintenanceRequests: $maintenanceRequests,
            communications: $communications,
        );
    }

    public function tenantDisplayName(): string
    {
        return $this->tenant->name ?? Str::headline($this->tenant->getKey());
    }

    public function tenancies(): Collection
    {
        return $this->tenancies;
    }

    public function upcomingPayments(): Collection
    {
        return $this->payments;
    }

    public function upcomingRentTotal(): float
    {
        return (float) $this->payments->sum('amount');
    }

    public function formattedUpcomingRentTotal(): string
    {
        return $this->formatCurrency($this->upcomingRentTotal());
    }

    public function maintenanceRequests(): Collection
    {
        return $this->maintenanceRequests;
    }

    public function communications(): Collection
    {
        return $this->communications;
    }

    public function formatCurrency(float $amount): string
    {
        return '£' . number_format($amount, 2);
    }
}
