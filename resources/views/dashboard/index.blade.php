@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="fw-bold mb-4">Dashboard</h1>

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Properties</h5>
                    <p class="card-text fs-4">{{ $stats['property_count'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Tenancies</h5>
                    <p class="card-text fs-4">{{ $stats['tenancy_count'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Leads</h5>
                    <p class="card-text fs-4">{{ $stats['lead_count'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Payments</h5>
                    <p class="card-text fs-4">{{ $stats['payment_count'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <canvas id="modelCountsChart"></canvas>
        </div>
        <div class="col-md-6 mb-4">
            <canvas id="paymentsChart"></canvas>
        </div>
    </div>

    <div id="stats-data" data-stats='@json($stats)' class="d-none"></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@vite('resources/js/dashboard.js')
@endsection

