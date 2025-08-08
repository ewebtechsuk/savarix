@extends('layouts.app')

@section('content')
<div class="container py-4">
    <form method="GET" action="{{ route('properties.index') }}" class="mb-4">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search">
            </div>
            <div class="col-md-4">
                <input type="text" name="origin" value="{{ request('origin') }}" class="form-control" placeholder="Origin address">
            </div>
            <div class="col-md-2">
                <input type="number" name="radius" value="{{ request('radius') }}" class="form-control" placeholder="Radius (km)">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </div>
    </form>
    <div class="row">
        @forelse($properties as $property)
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    @if($property->photo)
                        <img src="{{ asset('storage/' . $property->photo) }}" class="card-img-top" alt="Photo" style="height:150px;object-fit:cover;">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $property->title }}</h5>
                        <p class="card-text">{{ $property->address }}, {{ $property->city }}</p>
                        <a href="{{ route('properties.show', $property) }}" class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                </div>
            </div>
        @empty
            <p>No properties found.</p>
        @endforelse
    </div>
</div>
@endsection
