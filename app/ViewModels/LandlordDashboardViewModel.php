<?php

namespace App\ViewModels;

use App\Models\Contact;
use App\Models\ContactCommunication;
use App\Models\Landlord;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenancy;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LandlordDashboardViewModel
{
    public function __construct(
        protected Landlord $landlord,
        protected ?Contact $contact,
        protected Collection $properties,
        protected Collection $tenancies,
        protected Collection $payments,
        protected Collection $maintenanceRequests,
        protected Collection $communications,
    ) {
    }

    public static function fromLandlord(Landlord $landlord): self
    {
        $contact = Contact::query()
            ->when($landlord->contact_email, function ($query, $email) {
                $query->where('email', $email);
            })
            ->first();

        $properties = Property::query()
            ->when($contact, function ($query) use ($contact) {
                $query->where('landlord_id', $contact->getKey());
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->with('vendor')
            ->orderBy('title')
            ->get();

        $tenancies = Tenancy::query()
            ->with(['property', 'contact'])
            ->when($properties->isNotEmpty(), function ($query) use ($properties) {
                $query->whereIn('property_id', $properties->pluck('id'));
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->orderByDesc('start_date')
            ->get();

        $payments = Payment::query()
            ->with(['tenancy.property', 'tenancy.contact'])
            ->where('status', 'pending')
            ->when($tenancies->isNotEmpty(), function ($query) use ($tenancies) {
                $query->whereIn('tenancy_id', $tenancies->pluck('id'));
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->orderBy('created_at')
            ->take(5)
            ->get();

        $maintenanceRequests = MaintenanceRequest::query()
            ->with('property')
            ->when($properties->isNotEmpty(), function ($query) use ($properties) {
                $query->whereIn('property_id', $properties->pluck('id'));
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->whereNotIn('status', ['resolved', 'closed', 'completed'])
            ->orderByDesc('created_at')
            ->get();

        $communicationContactIds = Collection::make();

        if ($contact) {
            $communicationContactIds = $communicationContactIds->merge([$contact->getKey()]);
        }

        if ($tenancies->isNotEmpty()) {
            $communicationContactIds = $communicationContactIds->merge($tenancies->pluck('contact_id')->filter());
        }

        $communicationContactIds = $communicationContactIds->unique()->filter();

        $communications = ContactCommunication::query()
            ->with('contact')
            ->when($communicationContactIds->isNotEmpty(), function ($query) use ($communicationContactIds) {
                $query->whereIn('contact_id', $communicationContactIds);
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->latest()
            ->take(5)
            ->get();

        return new self(
            landlord: $landlord,
            contact: $contact,
            properties: $properties,
            tenancies: $tenancies,
            payments: $payments,
            maintenanceRequests: $maintenanceRequests,
            communications: $communications,
        );
    }

    public function landlordDisplayName(): string
    {
        if ($this->landlord->person_firstname || $this->landlord->person_lastname) {
            return trim($this->landlord->person_firstname . ' ' . $this->landlord->person_lastname);
        }

        return $this->landlord->person_company
            ?: ($this->contact?->name ?? Str::headline('landlord-' . $this->landlord->getKey()));
    }

    public function properties(): Collection
    {
        return $this->properties;
    }

    public function tenancies(): Collection
    {
        return $this->tenancies;
    }

    public function payments(): Collection
    {
        return $this->payments;
    }

    public function maintenanceRequests(): Collection
    {
        return $this->maintenanceRequests;
    }

    public function communications(): Collection
    {
        return $this->communications;
    }

    public function formattedPendingRentTotal(): string
    {
        return $this->formatCurrency((float) $this->payments->sum('amount'));
    }

    public function formatCurrency(float $amount): string
    {
        return '£' . number_format($amount, 2);
    }
}
