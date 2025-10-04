@extends('layouts.app')

@section('content')
<div class="container py-4">
    @php($properties = $dashboard->properties())
    @php($tenancies = $dashboard->tenancies())
    @php($openMaintenanceCount = $dashboard->maintenanceRequests()->count())
    @php($activeTenancies = $tenancies->filter(fn ($tenancy) => strtolower($tenancy->status) === 'active'))

    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-1">Welcome back, {{ $dashboard->landlordDisplayName() }}!</h1>
            <p class="text-muted mb-0">Here's a snapshot of your portfolio performance.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Managed properties</div>
                    <div class="display-6 fs-2">{{ $properties->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Active tenancies</div>
                    <div class="display-6 fs-2">{{ $activeTenancies->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Open maintenance</div>
                    <div class="display-6 fs-2">{{ $openMaintenanceCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Pending rent</div>
                    <div class="display-6 fs-2">{{ $dashboard->formattedPendingRentTotal() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header fw-semibold">Upcoming rent payments</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse ($dashboard->payments() as $payment)
                            <li class="list-group-item">
                                <div class="fw-semibold">{{ $payment->tenancy?->contact?->name ?? 'Tenancy #' . $payment->tenancy_id }}</div>
                                <div class="small text-muted">{{ $payment->tenancy?->property?->title ?? 'Property #' . $payment->tenancy?->property_id }}</div>
                                <div class="small text-muted">
                                    {{ $dashboard->formatCurrency((float) $payment->amount) }} · Due {{ optional($payment->created_at)->format('j M Y') ?? 'soon' }}
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No pending rent invoices.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header fw-semibold">Open maintenance</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse ($dashboard->maintenanceRequests() as $request)
                            <li class="list-group-item">
                                <div class="fw-semibold">{{ $request->property?->title ?? 'Maintenance #' . $request->id }}</div>
                                <div class="small text-muted">{{ ucfirst($request->status) }} · logged {{ optional($request->created_at)->format('j M Y') }}</div>
                                <p class="mb-0 small">{{ \Illuminate\Support\Str::limit($request->description, 90) }}</p>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No open issues reported.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header fw-semibold">Recent communications</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse ($dashboard->communications() as $communication)
                            <li class="list-group-item">
                                <div class="fw-semibold">{{ $communication->contact?->name ?? 'Contact #' . $communication->contact_id }}</div>
                                <div class="small text-muted">{{ optional($communication->created_at)->format('j M Y H:i') }}</div>
                                <p class="mb-0 small">{{ \Illuminate\Support\Str::limit($communication->communication, 110) }}</p>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No recent communications logged.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Active tenancy schedule</div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Property</th>
                                <th scope="col">Tenant</th>
                                <th scope="col">Start date</th>
                                <th scope="col">End date</th>
                                <th scope="col">Rent</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tenancies as $tenancy)
                                <tr>
                                    <td>{{ $tenancy->property?->title ?? 'Property #' . $tenancy->property_id }}</td>
                                    <td>{{ $tenancy->contact?->name ?? 'Contact #' . $tenancy->contact_id }}</td>
                                    <td>{{ optional($tenancy->start_date)->format('j M Y') }}</td>
                                    <td>{{ optional($tenancy->end_date)->format('j M Y') ?? 'Ongoing' }}</td>
                                    <td>{{ $dashboard->formatCurrency((float) $tenancy->rent_amount) }}</td>
                                    <td>{{ ucfirst($tenancy->status) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No tenancies found for your properties.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Property summary</div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Property</th>
                                <th scope="col">City</th>
                                <th scope="col">Status</th>
                                <th scope="col">Bedrooms</th>
                                <th scope="col">Bathrooms</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($properties as $property)
                                <tr>
                                    <td>{{ $property->title }}</td>
                                    <td>{{ $property->city ?? '—' }}</td>
                                    <td>{{ ucfirst($property->status) }}</td>
                                    <td>{{ $property->bedrooms ?? '—' }}</td>
                                    <td>{{ $property->bathrooms ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No properties assigned to your account.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
