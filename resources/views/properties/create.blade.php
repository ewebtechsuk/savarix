@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">Add Property</div>
                <div class="card-body">
                    <form action="{{ route('properties.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="MAX_FILE_SIZE" value="33554432" />
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" name="price" class="form-control" step="0.01">
                            </div>
                            <div class="col">
                                <label for="type" class="form-label">Type</label>
                                <input type="text" name="type" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label for="bedrooms" class="form-label">Bedrooms</label>
                                <input type="number" name="bedrooms" class="form-control">
                            </div>
                            <div class="col">
                                <label for="bathrooms" class="form-label">Bathrooms</label>
                                <input type="number" name="bathrooms" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" name="address" class="form-control">
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label for="city" class="form-label">City</label>
                                <input type="text" name="city" class="form-control">
                            </div>
                            <div class="col">
                                <label for="postcode" class="form-label">Postcode</label>
                                <input type="text" name="postcode" class="form-control">
                            </div>
                        </div>
                        @if(auth()->user() && auth()->user()->is_admin)
                        <div class="mb-3">
                            <label for="vendor_id" class="form-label">Vendor</label>
                            <input type="number" name="vendor_id" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="landlord_id" class="form-label">Landlord</label>
                            <select name="landlord_id" class="form-select">
                                <option value="">Select landlord</option>
                                @foreach(\App\Models\Contact::where('type', 'landlord')->orderBy('name')->get() as $landlord)
                                    <option value="{{ $landlord->id }}" {{ (isset($landlord_id) && $landlord_id == $landlord->id) ? 'selected' : '' }}>{{ $landlord->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="applicant_id" class="form-label">Applicant</label>
                            <input type="number" name="applicant_id" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="document" class="form-label">Document</label>
                            <input type="file" name="document" class="form-control">
                        </div>
                        @endif
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="available">Available</option>
                                <option value="sold">Sold</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="media" class="form-label">Media Gallery</label>
                            <input type="file" name="media[]" multiple class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="features" class="form-label">Property Features</label>
                            <div class="row">
                                @foreach($featuresList as $feature)
                                    <div class="col-6 col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="features[]" value="{{ $feature }}" id="feature_{{ md5($feature) }}">
                                            <label class="form-check-label" for="feature_{{ md5($feature) }}">{{ $feature }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Create</button>
                        <a href="{{ route('properties.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
