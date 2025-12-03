@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark fw-bold">Edit Property</div>
                <div class="card-body">
                    <form action="{{ route('properties.update', $property) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ $property->title }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" class="form-control">{{ $property->description }}</textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" name="price" class="form-control" step="0.01" value="{{ $property->price }}">
                            </div>
                            <div class="col">
                                <label for="type" class="form-label">Type</label>
                                <input type="text" name="type" class="form-control" value="{{ $property->type }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label for="bedrooms" class="form-label">Bedrooms</label>
                                <input type="number" name="bedrooms" class="form-control" value="{{ $property->bedrooms }}">
                            </div>
                            <div class="col">
                                <label for="bathrooms" class="form-label">Bathrooms</label>
                                <input type="number" name="bathrooms" class="form-control" value="{{ $property->bathrooms }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" value="{{ $property->address }}">
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label for="city" class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="{{ $property->city }}">
                            </div>
                            <div class="col">
                                <label for="postcode" class="form-label">Postcode</label>
                                <input type="text" name="postcode" class="form-control" value="{{ $property->postcode }}">
                            </div>
                        </div>
                        @if(auth()->user() && auth()->user()->is_admin)
                        <div class="mb-3">
                            <label for="vendor_id" class="form-label">Vendor</label>
                            <input type="number" name="vendor_id" class="form-control" value="{{ $property->vendor_id }}">
                        </div>
                        <div class="mb-3">
                            <label for="landlord_id" class="form-label">Landlord</label>
                            <div class="input-group">
                                <select name="landlord_id" id="landlord_id" class="form-select">
                                    <option value="">Select landlord</option>
                                    @foreach(\App\Models\Contact::where('type', 'landlord')->orderBy('name')->get() as $landlord)
                                        <option value="{{ $landlord->id }}" {{ $property->landlord_id == $landlord->id ? 'selected' : '' }}>{{ $landlord->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#landlordSearchModal">Add Landlord</button>
                            </div>
                        </div>

                        <!-- Landlord Search Modal -->
                        <div class="modal fade" id="landlordSearchModal" tabindex="-1" aria-labelledby="landlordSearchModalLabel" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="landlordSearchModalLabel">Search Landlord</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                <input type="text" id="landlordSearchBox" class="form-control mb-3" placeholder="Type landlord name...">
                                <ul class="list-group" id="landlordSearchResults"></ul>
                              </div>
                            </div>
                          </div>
                        </div>

                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const searchBox = document.getElementById('landlordSearchBox');
                            const resultsList = document.getElementById('landlordSearchResults');
                            const landlordSelect = document.getElementById('landlord_id');
                            if (searchBox && resultsList && landlordSelect) {
                                searchBox.addEventListener('input', function() {
                                    const query = searchBox.value.trim();
                                    if (query.length < 2) {
                                        resultsList.innerHTML = '';
                                        return;
                                    }
                                    fetch("{{ url('/contacts/search') }}?type=landlord&q=" + encodeURIComponent(query))
                                        .then(res => res.json())
                                        .then(data => {
                                            resultsList.innerHTML = '';
                                            data.forEach(function(landlord) {
                                                const li = document.createElement('li');
                                                li.className = 'list-group-item list-group-item-action';
                                                li.textContent = landlord.text || landlord.name;
                                                li.style.cursor = 'pointer';
                                                li.onclick = function() {
                                                    landlordSelect.value = landlord.id;
                                                    document.querySelector('#landlordSearchModal .btn-close').click();
                                                };
                                                resultsList.appendChild(li);
                                            });
                                        });
                                });
                            }
                        });
                        </script>
                        <div class="mb-3">
                            <label for="applicant_id" class="form-label">Applicant</label>
                            <input type="number" name="applicant_id" class="form-control" value="{{ $property->applicant_id }}">
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" class="form-control">{{ $property->notes }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="document" class="form-label">Document</label>
                            <input type="file" name="document" class="form-control">
                        </div>
                        @endif
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="available" @if($property->status == 'available') selected @endif>Available</option>
                                <option value="sold" @if($property->status == 'sold') selected @endif>Sold</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            @if($property->photo)
                                <img src="{{ asset('storage/' . $property->photo) }}" alt="Photo" class="rounded mt-2" width="100">
                            @endif
                        </div>
                        <hr>
                        <h5>Media Gallery</h5>
                        <div class="row g-2 mb-3">
                            @foreach($property->media as $media)
                                <div class="col-4 col-md-3">
                                    <div class="card">
                                        <img src="{{ asset('storage/' . $media->file_path) }}" class="card-img-top" alt="Media" style="height:120px;object-fit:cover;">
                                        <div class="card-body p-2">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="featured_media" value="{{ $media->id }}" @checked($media->is_featured)>
                                                <label class="form-check-label">Featured image</label>
                                            </div>
                                            <label class="form-label small">Display order</label>
                                            <input type="number" class="form-control form-control-sm" name="media_order[{{ $media->id }}]" value="{{ $media->order }}" min="0">
                                        </div>
                                        <form action="{{ route('properties.media.destroy', [$property, $media]) }}" method="POST" onsubmit="return confirm('Delete this image?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger w-100">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mb-3">
                            <label for="media" class="form-label">Add Images</label>
                            <input type="file" name="media[]" multiple class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="features" class="form-label">Property Features</label>
                            <div class="row">
                                @foreach($featuresList as $feature)
                                    <div class="col-6 col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="features[]" value="{{ $feature }}" id="feature_{{ md5($feature) }}" @if(in_array($feature, $selectedFeatures)) checked @endif>
                                            <label class="form-check-label" for="feature_{{ md5($feature) }}">{{ $feature }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @php
                            $latestMarketingNote = collect(data_get($property->activity_log, 'marketing_notes', []))->last();
                        @endphp
                        <div class="mb-3">
                            <h5>Marketing</h5>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="publish_to_portal" id="publish_to_portal" value="1" @checked(old('publish_to_portal', $property->publish_to_portal))>
                                <label class="form-check-label" for="publish_to_portal">Publish to portal syndication</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="send_marketing_campaign" id="send_marketing_campaign" value="1" @checked(old('send_marketing_campaign', $property->send_marketing_campaign))>
                                <label class="form-check-label" for="send_marketing_campaign">Include in active marketing campaigns</label>
                            </div>
                            <label for="marketing_notes" class="form-label">Add marketing note</label>
                            <textarea name="marketing_notes" id="marketing_notes" class="form-control" rows="3" placeholder="Record portal updates, brochure changes or campaign ideas"></textarea>
                            @if($latestMarketingNote)
                                <div class="form-text">Last note on {{ isset($latestMarketingNote['recorded_at']) ? \Carbon\Carbon::parse($latestMarketingNote['recorded_at'])->format('j M Y H:i') : 'unknown date' }}: "{{ $latestMarketingNote['note'] ?? '—' }}"</div>
                            @endif
                        </div>
                        @if($matches->isNotEmpty())
                            <div class="mb-3">
                                <h5>Applicant matches</h5>
                                <p class="text-muted small">Ranked suggestions based on budget, bedrooms and location.</p>
                                <ul class="list-group">
                                    @foreach($matches as $match)
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="fw-semibold">{{ $match['applicant']->name }}</div>
                                                <div class="small text-muted">Budget £{{ number_format($match['applicant']->min_budget ?? 0) }} - £{{ number_format($match['applicant']->max_budget ?? 0) }}</div>
                                                <div class="small text-muted">Prefers {{ $match['applicant']->preferred_bedrooms ?? 'n/a' }} beds · {{ $match['applicant']->preferred_city ?? 'Any city' }}</div>
                                            </div>
                                            <span class="badge bg-primary rounded-pill">{{ $match['score'] }}%</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <button type="submit" class="btn btn-warning">Update</button>
                        <a href="{{ route('properties.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
