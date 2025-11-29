<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PartnerIntegration;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyPortals;
use App\Models\SavarixTenancy;
use Carbon\Carbon;

class UnifiedAgencyDashboardService
{
    /**
     * Build a unified snapshot of sales, lettings, accounting, and portal metrics.
     */
    public function getSummary(): array
    {
        return [
            'generated_at' => Carbon::now()->toIso8601String(),
            'sales' => $this->summarisePropertiesByType('sales'),
            'lettings' => $this->summariseLettings(),
            'accounting' => $this->summariseAccounting(),
            'portal_publications' => $this->summarisePortalPublications(),
            'active_integrations' => $this->summariseActiveIntegrations(),
        ];
    }

    protected function summarisePropertiesByType(string $type): array
    {
        $query = Property::query()->where('type', $type);

        $statusBreakdown = (clone $query)
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->filter()
            ->map(fn ($count) => (int) $count)
            ->toArray();

        return [
            'total' => (clone $query)->count(),
            'pipeline_value' => (clone $query)->sum('price'),
            'status_breakdown' => $statusBreakdown,
            'portal_ready' => (clone $query)->where('publish_to_portal', true)->count(),
        ];
    }

    protected function summariseLettings(): array
    {
        $tenancies = SavarixTenancy::query();

        $statusBreakdown = (clone $tenancies)
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->filter()
            ->map(fn ($count) => (int) $count)
            ->toArray();

        $upcomingRenewals = (clone $tenancies)
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [Carbon::now(), Carbon::now()->addMonth()])
            ->count();

        return [
            'active' => (clone $tenancies)->where('status', 'active')->count(),
            'total' => (clone $tenancies)->count(),
            'monthly_rent_roll' => (clone $tenancies)->sum('rent'),
            'status_breakdown' => $statusBreakdown,
            'upcoming_renewals' => $upcomingRenewals,
        ];
    }

    protected function summariseAccounting(): array
    {
        $invoices = Invoice::query();
        $payments = Payment::query();

        $outstanding = (clone $invoices)->where('status', '!=', 'paid')->sum('amount');
        $overdue = (clone $invoices)
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '<', Carbon::today())
            ->sum('amount');

        return [
            'open_invoices' => (clone $invoices)->where('status', '!=', 'paid')->count(),
            'outstanding_balance' => $outstanding,
            'overdue_balance' => $overdue,
            'paid_to_date' => (clone $invoices)->where('status', 'paid')->sum('amount'),
            'payments_cleared' => (clone $payments)->where('status', 'completed')->sum('amount'),
        ];
    }

    protected function summarisePortalPublications(): array
    {
        $publishedProperties = Property::query()->where('publish_to_portal', true)->count();

        $portalMatrix = PropertyPortals::query()->get(PropertyPortals::PORTALS);

        $portalCounts = $portalMatrix->reduce(function (array $carry, PropertyPortals $record) {
            foreach (PropertyPortals::PORTALS as $portalKey) {
                $carry[$portalKey] = ($carry[$portalKey] ?? 0) + (int) $record->{$portalKey};
            }

            return $carry;
        }, []);

        ksort($portalCounts);

        return [
            'total_published' => $publishedProperties,
            'distribution' => $portalCounts,
        ];
    }

    protected function summariseActiveIntegrations(): array
    {
        return PartnerIntegration::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'provider', 'type'])
            ->map(fn (PartnerIntegration $integration) => $integration->toArray())
            ->values()
            ->all();
    }
}

