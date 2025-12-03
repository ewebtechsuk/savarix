@extends('layouts.app')

{{-- Ensure jQuery is loaded before Select2 and custom scripts --}}
@include('contacts._jquery')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white fw-bold">Contact Details</div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="contactTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="main-tab" data-bs-toggle="tab" data-bs-target="#main" type="button" role="tab">Main Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab">Notes</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="financial-tab" data-bs-toggle="tab" data-bs-target="#financial" type="button" role="tab">Financial</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="statements-tab" data-bs-toggle="tab" data-bs-target="#statements" type="button" role="tab">Statements</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="leads-tab" data-bs-toggle="tab" data-bs-target="#leads" type="button" role="tab">Leads</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="applicants-tab" data-bs-toggle="tab" data-bs-target="#applicants" type="button" role="tab">Applicants</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="referrals-tab" data-bs-toggle="tab" data-bs-target="#referrals" type="button" role="tab">Referrals</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="properties-tab" data-bs-toggle="tab" data-bs-target="#properties" type="button" role="tab">Properties</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="management-tab" data-bs-toggle="tab" data-bs-target="#management" type="button" role="tab">Management</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="offers-tab" data-bs-toggle="tab" data-bs-target="#offers" type="button" role="tab">Offers</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="viewings-tab" data-bs-toggle="tab" data-bs-target="#viewings" type="button" role="tab">Viewings</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="communications-tab" data-bs-toggle="tab" data-bs-target="#communications" type="button" role="tab">Communications</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="callreminders-tab" data-bs-toggle="tab" data-bs-target="#callreminders" type="button" role="tab">Call Reminders</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">Documents</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button" role="tab">Tasks</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="clientportal-tab" data-bs-toggle="tab" data-bs-target="#clientportal" type="button" role="tab">Client Portal</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline" type="button" role="tab">Timeline</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="contactTabsContent">
                        <div class="tab-pane fade show active" id="main" role="tabpanel">
                            <h2 class="fw-bold">{{ $contact->name }}</h2>
                            <p><strong>Type:</strong> {{ ucfirst($contact->type) }}</p>
                            <p><strong>Email:</strong> {{ $contact->email }}</p>
                            <p><strong>Phone:</strong> {{ $contact->phone }}</p>
                            <p><strong>Address:</strong> {{ $contact->address }}</p>
                            @php
                                $asCollection = fn ($value) => $value instanceof \Illuminate\Support\Collection ? $value : collect($value ?? []);

                                $groups = $asCollection($contact->getRelation('groups'));
                                $tags = $asCollection($contact->getRelation('tags'));
                                $properties = $asCollection($contact->getRelation('properties'));
                                $notes = $asCollection($contact->getRelation('notes'));
                                $communications = $asCollection($contact->getRelation('communications'));
                                $viewings = $asCollection($contact->getRelation('viewings'));
                                $offers = $asCollection($contact->getRelation('offers'));
                                $tenancies = $asCollection($contact->getRelation('tenancies'));
                            @endphp
                            <p><strong>Groups:</strong>
                                @if($groups->isNotEmpty())
                                    @foreach($groups as $group)
                                        <span class="badge bg-info me-1">{{ $group->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No groups assigned</span>
                                @endif
                            </p>
                            <p><strong>Tags:</strong>
                                @if($tags->isNotEmpty())
                                    @foreach($tags as $tag)
                                        <span class="badge bg-secondary me-1">{{ $tag->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No tags assigned</span>
                                @endif
                            </p>
                        </div>
                        <div class="tab-pane fade" id="notes" role="tabpanel">
                            <h5>Notes</h5>
                            <form action="{{ route('contacts.addNote', $contact) }}" method="POST" class="mb-3">
                                @csrf
                                <div class="input-group">
                                    <input type="text" name="note" class="form-control" placeholder="Add a new note...">
                                    <button class="btn btn-primary" type="submit">Add</button>
                                </div>
                            </form>
                            <ul class="list-group">
                                @forelse($notes as $note)
                                    <li class="list-group-item d-flex justify-content-between align-items-center"
                                        data-update-url="{{ route('contacts.notes.inline.update', [$contact, $note]) }}"
                                        data-delete-url="{{ route('contacts.notes.inline.destroy', [$contact, $note]) }}">
                                        <span class="note-text">{{ $note->note }}</span>
                                        <span>
                                            <button class="btn btn-sm btn-outline-secondary me-1 edit-note-btn" data-id="{{ $note->id }}">Edit</button>
                                            <button class="btn btn-sm btn-success me-1 save-note-btn" data-id="{{ $note->id }}" style="display:none;">Save</button>
                                            <button class="btn btn-sm btn-outline-danger delete-note-btn" data-id="{{ $note->id }}">Delete</button>
                                        </span>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">No notes for this contact.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="tab-pane fade" id="financial" role="tabpanel">
                            <h5>Financial Information</h5>
                            <ul class="list-group mb-3">
                                <li class="list-group-item"><strong>Outstanding Balance:</strong> £0.00</li>
                                <li class="list-group-item"><strong>Last Payment:</strong> N/A</li>
                                <li class="list-group-item"><strong>Account Status:</strong> Active</li>
                            </ul>
                        </div>
                        <div class="tab-pane fade" id="statements" role="tabpanel">
                            <h5>Statements</h5>
                            <p class="text-muted">No statements available.</p>
                        </div>
                        <div class="tab-pane fade" id="leads" role="tabpanel">
                            <h5>Leads</h5>
                            <p class="text-muted">No leads found for this contact.</p>
                        </div>
                        <div class="tab-pane fade" id="applicants" role="tabpanel">
                            <h5>Applicants</h5>
                            <p class="text-muted">No applicant records for this contact.</p>
                        </div>
                        <div class="tab-pane fade" id="referrals" role="tabpanel">
                            <h5>Referrals</h5>
                            <p class="text-muted">No referrals for this contact.</p>
                        </div>
                        <div class="tab-pane fade" id="properties" role="tabpanel">
                            @if($contact->type === 'landlord')
                                <form action="{{ route('contacts.assignProperty', $contact) }}" method="POST" class="mb-3" id="assign-property-form">
                                    @csrf
                                    <label for="property-select" class="form-label">Assign property to this landlord:</label>
                                    <select id="property-select" name="property_id" class="form-select" style="width:100%" data-placeholder="Search properties"></select>
                                    <button type="submit" class="btn btn-primary mt-2">Assign</button>
                                </form>
                                @if($properties->count())
                                    <ul class="list-group">
                                        @foreach($properties as $property)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $property->title ?? 'Property #' . $property->id }}</strong><br>
                                                    <span>{{ $property->address }}</span>
                                                </div>
                                                <a href="{{ route('properties.show', $property) }}" class="btn btn-sm btn-outline-primary">View</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>No properties found for this contact.</p>
                                @endif
                            @else
                                <p class="text-muted">Property assignment is available only for landlord contacts.</p>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="management" role="tabpanel">
                            <h5>Management</h5>
                            @if($tenancies->isEmpty())
                                <p class="text-muted">No tenancies linked to this contact.</p>
                            @else
                                <ul class="list-group">
                                    @foreach($tenancies as $tenancy)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-semibold">{{ $tenancy->property->title ?? 'Property #'.$tenancy->property_id }}</div>
                                                <div class="small text-muted">{{ optional($tenancy->start_date)->toDateString() }} - {{ optional($tenancy->end_date)->toDateString() ?? 'ongoing' }}</div>
                                            </div>
                                            <span class="badge bg-success">{{ ucfirst($tenancy->status ?? 'active') }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="offers" role="tabpanel">
                            <h5>Offers</h5>
                            @if($offers->isEmpty())
                                <p class="text-muted">No offers for this contact.</p>
                            @else
                                <ul class="list-group">
                                    @foreach($offers as $offer)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-semibold">£{{ number_format($offer->amount, 2) }} — {{ $offer->property->title ?? 'Property #'.$offer->property_id }}</div>
                                                <div class="small text-muted">{{ optional($offer->offered_at)->format('j M Y H:i') ?? 'Pending timestamp' }}</div>
                                            </div>
                                            <span class="badge bg-info text-dark">{{ ucfirst($offer->status) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="viewings" role="tabpanel">
                            <h5>Viewings</h5>
                            @if($errors->has('date'))
                                <div class="alert alert-warning">{{ $errors->first('date') }}</div>
                            @endif
                            <form action="{{ route('contacts.addViewing', $contact) }}" method="POST" class="mb-3">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <select name="property_id" id="viewing-property" class="form-select" style="width:100%" data-placeholder="Search properties"></select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="datetime-local" name="date" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-primary w-100" type="submit">Add Viewing</button>
                                    </div>
                                </div>
                            </form>
                            <ul class="list-group">
                                @forelse($viewings as $viewing)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Viewing for <strong>{{ $viewing->property->title ?? 'Property #' . $viewing->property_id }}</strong> on <span>{{ $viewing->date }}</span>
                                        <span>
                                            <a href="{{ route('contacts.editViewing', [$contact, $viewing]) }}" class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                                            <form action="{{ route('contacts.viewings.destroy', [$contact, $viewing]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this viewing?')">Delete</button>
                                            </form>
                                        </span>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">No viewings for this contact.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="tab-pane fade" id="communications" role="tabpanel">
                            <h5>Communications</h5>
                            <form action="{{ route('contacts.addCommunication', $contact) }}" method="POST" class="mb-3">
                                @csrf
                                <div class="input-group">
                                    <input type="text" name="communication" class="form-control" placeholder="Log a new communication...">
                                    <button class="btn btn-primary" type="submit">Add</button>
                                </div>
                            </form>
                            <ul class="list-group">
                                @forelse($communications as $comm)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $comm->communication }}
                                        <span>
                                            <a href="{{ route('contacts.editCommunication', [$contact, $comm]) }}" class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                                            <form action="{{ route('contacts.communications.destroy', [$contact, $comm]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this communication?')">Delete</button>
                                            </form>
                                        </span>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">No communications for this contact.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="tab-pane fade" id="callreminders" role="tabpanel">
                            <h5>Call Reminders</h5>
                            <p class="text-muted">No call reminders set for this contact.</p>
                        </div>
                        <div class="tab-pane fade" id="documents" role="tabpanel">
                            <h5>Documents</h5>
                            <p class="text-muted">No documents uploaded for this contact.</p>
                        </div>
                        <div class="tab-pane fade" id="tasks" role="tabpanel">
                            <h5>Tasks</h5>
                            <p class="text-muted">No tasks assigned to this contact.</p>
                        </div>
                        <div class="tab-pane fade" id="clientportal" role="tabpanel">
                            <h5>Client Portal</h5>
                            <p class="text-muted">No client portal activity for this contact.</p>
                        </div>
                        <div class="tab-pane fade" id="timeline" role="tabpanel">
                            <h5>Timeline</h5>
                            <p class="text-muted">No timeline events for this contact.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        @if($contact->type === 'landlord')
                            <a href="{{ route('properties.create', ['landlord_id' => $contact->id]) }}" class="btn btn-primary">Add property for this landlord</a>
                        @endif
                        <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('contacts.destroy', $contact) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                        <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';
    const propertySearchUrl = '{{ route('contacts.properties.search') }}';

    function select2Config(placeholder) {
        return {
            placeholder: placeholder,
            width: '100%',
            ajax: {
                url: propertySearchUrl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            },
            minimumInputLength: 1
        };
    }

    $('#property-select').select2(select2Config('Search properties to assign'));
    $('#viewing-property').select2(select2Config('Select viewing property'));

    $(document).on('click', '.edit-note-btn', function() {
        var $li = $(this).closest('li');
        var noteText = $li.find('.note-text').text();
        $li.find('.note-text').replaceWith('<input type="text" class="form-control note-edit-input" value="'+noteText+'" style="width:60%">');
        $(this).hide();
        $li.find('.save-note-btn').show();
    });

    $(document).on('click', '.save-note-btn', function() {
        var $li = $(this).closest('li');
        var updateUrl = $li.data('update-url');
        var newText = $li.find('.note-edit-input').val();
        $.ajax({
            url: updateUrl,
            type: 'PATCH',
            data: { note: newText, _token: csrfToken },
            success: function(resp) {
                $li.find('.note-edit-input').replaceWith('<span class="note-text">'+resp.note+'</span>');
                $li.find('.save-note-btn').hide();
                $li.find('.edit-note-btn').show();
            }
        });
    });

    $(document).on('click', '.delete-note-btn', function() {
        var $li = $(this).closest('li');
        var deleteUrl = $li.data('delete-url');
        if(confirm('Delete this note?')) {
            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                data: { _token: csrfToken },
                success: function() { $li.remove(); }
            });
        }
    });
});
</script>
@endpush
