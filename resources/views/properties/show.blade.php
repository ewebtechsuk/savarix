@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white fw-bold">Property Details</div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="propertyTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="main-tab" data-bs-toggle="tab" data-bs-target="#main" type="button" role="tab">Main Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="media-tab" data-bs-toggle="tab" data-bs-target="#media" type="button" role="tab">Media</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="features-tab" data-bs-toggle="tab" data-bs-target="#features" type="button" role="tab">Features</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="landlord-tab" data-bs-toggle="tab" data-bs-target="#landlord" type="button" role="tab">Landlord</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="financial-tab" data-bs-toggle="tab" data-bs-target="#financial" type="button" role="tab">Financial</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="statements-tab" data-bs-toggle="tab" data-bs-target="#statements" type="button" role="tab">Statements</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="offers-tab" data-bs-toggle="tab" data-bs-target="#offers" type="button" role="tab">Offers</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="viewings-tab" data-bs-toggle="tab" data-bs-target="#viewings" type="button" role="tab">Viewings</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="management-tab" data-bs-toggle="tab" data-bs-target="#management" type="button" role="tab">Management</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="communications-tab" data-bs-toggle="tab" data-bs-target="#communications" type="button" role="tab">Communications</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">Documents</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button" role="tab">Tasks</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline" type="button" role="tab">Timeline</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="marketing-tab" data-bs-toggle="tab" data-bs-target="#marketing" type="button" role="tab">Marketing</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="propertyTabsContent">
                        <div class="tab-pane fade show active" id="main" role="tabpanel">
                            <h2 class="fw-bold">{{ $property->title }}</h2>
                            @if($property->photo)
                                <img src="{{ asset('storage/' . $property->photo) }}" alt="Photo" class="rounded mb-3" width="200">
                            @endif
                            <p><strong>Description:</strong> {{ $property->description }}</p>
                            <div class="row mb-2">
                                <div class="col"><strong>Price:</strong> £{{ number_format($property->price, 2) }}</div>
                                <div class="col"><strong>Type:</strong> {{ config('property.property_types')[$property->type] ?? ucfirst($property->type) }}</div>
                                <div class="col"><strong>Status:</strong> <span class="badge bg-{{ $property->status == 'available' ? 'success' : 'secondary' }}">{{ ucfirst($property->status) }}</span></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col"><strong>Bedrooms:</strong> {{ $property->bedrooms }}</div>
                                <div class="col"><strong>Bathrooms:</strong> {{ $property->bathrooms }}</div>
                            </div>
                            <div class="mb-2"><strong>Address:</strong> {{ $property->address }}, {{ $property->city }}, {{ $property->postcode }}</div>
                            @if($property->latitude && $property->longitude)
                                <div id="property-map" style="height: 300px;" class="mb-3"></div>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="media" role="tabpanel">
                            <h5>Media Gallery</h5>
                            @if($property->media->isNotEmpty())
                                <div id="mediaCarousel" class="carousel slide mb-3" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        @foreach($property->media as $index => $media)
                                            <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                                <img src="{{ asset('storage/' . $media->file_path) }}" class="d-block w-100" alt="Media">
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($property->media->count() > 1)
                                        <button class="carousel-control-prev" type="button" data-bs-target="#mediaCarousel" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#mediaCarousel" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <p class="text-muted">No media uploaded.</p>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="features" role="tabpanel">
                            <h5>Property Features</h5>
                            <ul class="list-group mb-3">
                                @forelse($features as $feature)
                                    <li class="list-group-item">{{ $feature }}</li>
                                @empty
                                    <li class="list-group-item text-muted">No features set</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="tab-pane fade" id="landlord" role="tabpanel">
                            <h5>Assign or Change Landlord</h5>
                            <form action="{{ route('properties.assignLandlord', $property) }}" method="POST" class="mb-3">
                                @csrf
                                <label for="landlord-select" class="form-label">Select Landlord:</label>
                                <select id="landlord-select" name="landlord_id" class="form-select" style="width:100%">
                                    <option value="">-- Search and select landlord --</option>
                                </select>
                                <button type="submit" class="btn btn-primary mt-2">Assign</button>
                            </form>
                            @if($property->landlord)
                                <h5>Landlord Details</h5>
                                <p><strong>Name:</strong> {{ $property->landlord->name }}</p>
                                <p><strong>Email:</strong> {{ $property->landlord->email }}</p>
                                <p><strong>Phone:</strong> {{ $property->landlord->phone }}</p>
                                <a href="{{ route('contacts.show', $property->landlord) }}" class="btn btn-sm btn-outline-primary">View Landlord</a>
                            @else
                                <p>No landlord assigned.</p>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="financial" role="tabpanel">
                            <h5>Financial Information</h5>
                            <ul class="list-group mb-3">
                                <li class="list-group-item"><strong>Price:</strong> £{{ number_format($property->price, 2) }}</li>
                                <li class="list-group-item"><strong>Status:</strong> {{ ucfirst($property->status) }}</li>
                                <li class="list-group-item"><strong>Type:</strong> {{ config('property.property_types')[$property->type] ?? ucfirst($property->type) }}</li>
                            </ul>
                        </div>
                        <div class="tab-pane fade" id="statements" role="tabpanel">
                            <h5>Statements</h5>
                            <p class="text-muted">No statements available.</p>
                        </div>
                        <div class="tab-pane fade" id="offers" role="tabpanel">
                            <h5>Offers</h5>
                            <p class="text-muted">No offers available.</p>
                        </div>
                        <div class="tab-pane fade" id="viewings" role="tabpanel">
                            <h5>Viewings</h5>
                            <p class="text-muted">No viewings scheduled.</p>
                        </div>
                        <div class="tab-pane fade" id="management" role="tabpanel">
                            <h5>Management</h5>
                            <p class="text-muted">No management data available.</p>
                        </div>
                        <div class="tab-pane fade" id="communications" role="tabpanel">
                            <h5>Communications</h5>
                            <p class="text-muted">No communications found.</p>
                        </div>
                        <div class="tab-pane fade" id="documents" role="tabpanel">
                            <h5>Documents</h5>
                            <form action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data" class="mb-3">
                                @csrf
                                <input type="hidden" name="documentable_type" value="App\\Models\\Property">
                                <input type="hidden" name="documentable_id" value="{{ $property->id }}">
                                <div class="input-group">
                                    <input type="file" name="file" class="form-control" required>
                                    <button class="btn btn-primary">Upload</button>
                                </div>
                            </form>
                            <ul class="list-group">
                                @forelse($property->documents as $document)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $document->name }}
                                        <span>
                                            <a href="{{ route('documents.download', $document) }}" class="btn btn-sm btn-outline-secondary">Download</a>
                                            <form action="{{ route('documents.sign', $document) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-primary">Sign</button>
                                            </form>
                                            @if($document->signed_at)
                                                <span class="badge bg-success ms-1">Signed</span>
                                            @endif
                                        </span>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">No documents uploaded.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="tab-pane fade" id="tasks" role="tabpanel">
                            <h5>Tasks</h5>
                            <p class="text-muted">No tasks assigned.</p>
                        </div>
                        <div class="tab-pane fade" id="timeline" role="tabpanel">
                            <h5>Timeline</h5>
                            <p class="text-muted">No timeline events.</p>
                        </div>
                        <div class="tab-pane fade" id="marketing" role="tabpanel">
                            <h5>Marketing</h5>
                            <p class="text-muted">No marketing information set.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('properties.edit', $property) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('properties.destroy', $property) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                        <a href="{{ route('properties.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('property-map')) {
        const map = L.map('property-map').setView([{{ $property->latitude }}, {{ $property->longitude }}], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        L.marker([{{ $property->latitude }}, {{ $property->longitude }}]).addTo(map);
    }
    $('#landlord-select').select2({
        placeholder: 'Search for a landlord...',
        ajax: {
            url: '{{ route('contacts.search') }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term, type: 'landlord' };
            },
            processResults: function (data) {
                return {
                    results: data.map(function(item) {
                        return { id: item.id, text: item.name };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 1
    });
});
</script>
@endpush
