@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Landlord Dashboard</div>
                <div class="card-body space-y-4">
                    <h3 class="text-xl font-semibold">Welcome to your landlord portal!</h3>
                    <ul class="list-disc pl-5">
                        <li>Review tenant statements</li>
                        <li>Track maintenance requests</li>
                        <li>Message your tenants</li>
                        <li>Manage your profile</li>
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
