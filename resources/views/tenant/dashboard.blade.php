@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-1">Welcome back, {{ $dashboard->tenantDisplayName() }}!</h1>
            <p class="text-muted mb-0">Here's the latest activity across your tenancy.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Upcoming rent</span>
                    <span class="badge bg-primary">{{ $dashboard->formattedUpcomingRentTotal() }}</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse ($dashboard->upcomingPayments() as $payment)
                            <li class="list-group-item">
                                <div class="fw-semibold">{{ $payment->tenancy?->property?->title ?? 'Tenancy #' . $payment->tenancy_id }}</div>
                                <div class="small text-muted">
                                    {{ $dashboard->formatCurrency((float) $payment->amount) }} ·
                                    Due {{ optional($payment->created_at)->format('j M Y') ?? 'soon' }}
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No upcoming rent payments.</li>
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
                                <div class="fw-semibold">{{ $request->property?->title ?? 'Maintenance request #' . $request->id }}</div>
                                <div class="small text-muted">{{ ucfirst($request->status) }} · logged {{ optional($request->created_at)->format('j M Y') }}</div>
                                <p class="mb-0 small">{{ \Illuminate\Support\Str::limit($request->description, 90) }}</p>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No open maintenance requests 🎉</li>
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
                            <li class="list-group-item text-muted">No recent messages to show.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Tenancy overview</div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Property</th>
                                <th scope="col">Contact</th>
                                <th scope="col">Start date</th>
                                <th scope="col">End date</th>
                                <th scope="col">Rent</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dashboard->tenancies() as $tenancy)
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
                                    <td colspan="6" class="text-center text-muted py-4">No tenancy records available.</td>
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
