@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Tenant Directory</div>
                <div class="card-body">
                    <p>The following tenants are currently registered in Ressapp:</p>
                    <ul class="list-unstyled">
                        @forelse ($tenants as $tenant)
                            <li class="mb-3">
                                <strong>{{ $tenant['name'] }}</strong>
                                ({{ $tenant['slug'] }})
                                <ul class="ms-4">
                                    @foreach ($tenant['domains'] as $domain)
                                        <li>{{ $domain }}</li>
                                    @endforeach
                                </ul>
                            </li>
                        @empty
                            <li>No tenants are registered yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
