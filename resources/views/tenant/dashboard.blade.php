@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Tenant Dashboard</div>
                <div class="card-body space-y-4">
                    <h3 class="text-xl font-semibold">Welcome to your tenant portal!</h3>
                    <ul class="list-disc pl-5">
                        <li>View financial statements</li>
                        <li>Submit maintenance requests</li>
                        <li>Check messages from your landlord</li>
                        <li>Manage your profile details</li>
                    </ul>
                    <form method="POST" action="/logout" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-danger">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
