@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Contacts</h1>
        <p class="text-muted mb-0">Manage landlords, tenants, applicants and suppliers from a single workspace.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('contacts.create') }}" class="btn btn-primary">New contact</a>
        <a href="{{ route('properties.create') }}" class="btn btn-outline-secondary">Add property</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-uppercase text-muted small mb-2">Total contacts</h6>
                <div class="display-6 fw-semibold">{{ $totals['overall'] }}</div>
                <p class="text-muted small mb-0">{{ $totals['filtered'] !== $totals['overall'] ? $totals['filtered'] . ' matching filters' : 'Showing all records' }}</p>
            </div>
        </div>
    </div>
    @foreach($types as $typeKey => $typeLabel)
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline">
                        <h6 class="text-uppercase text-muted small mb-2">{{ $typeLabel }}</h6>
                        @if(($filters['type'] ?? null) === $typeKey)
                            <span class="badge bg-primary">Filtered</span>
                        @endif
                    </div>
                    <div class="display-6 fw-semibold">{{ $typeBreakdown[$typeKey] ?? 0 }}</div>
                    <p class="text-muted small mb-0">{{ ucfirst($typeKey) }} records</p>
                </div>
            </div>
        </div>
    @endforeach
</div>

<form method="GET" action="{{ route('contacts.index') }}" class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Name, email or company">
            </div>
            <div class="col-md-3 col-lg-2">
                <label for="type" class="form-label">Type</label>
                <select name="type" id="type" class="form-select">
                    <option value="all">All types</option>
                    @foreach($types as $typeKey => $typeLabel)
                        <option value="{{ $typeKey }}" @selected(($filters['type'] ?? 'all') === $typeKey)>{{ $typeLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-3">
                <label for="group" class="form-label">Group</label>
                <select name="group" id="group" class="form-select">
                    <option value="all">All groups</option>
                    @foreach($groupBreakdown as $group)
                        <option value="{{ $group->id }}" @selected(($filters['group'] ?? 'all') == $group->id)>
                            {{ $group->name }} ({{ $group->contacts_count }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-3">
                <label for="tag" class="form-label">Tag</label>
                <select name="tag" id="tag" class="form-select">
                    <option value="all">All tags</option>
                    @foreach($tagBreakdown as $tag)
                        <option value="{{ $tag->id }}" @selected(($filters['tag'] ?? 'all') == $tag->id)>
                            {{ $tag->name }} ({{ $tag->contacts_count }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-lg-1 d-grid">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
            <div class="col-md-2 col-lg-1 d-grid">
                <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </div>
</form>

<form method="POST" action="{{ route('contacts.bulk') }}" class="card border-0 shadow-sm">
    @csrf
    <div class="card-body">
        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-3">
                <label class="form-label">Bulk action</label>
                <select name="action" id="bulk-action" class="form-select">
                    <option value="">Select action</option>
                    <option value="email">Send email</option>
                    <option value="sms">Send SMS</option>
                    <option value="tag">Apply tag</option>
                    <option value="delete">Delete</option>
                </select>
            </div>
            <div class="col-md-3" data-action-field="tag" style="display:none;">
                <label for="bulk-tag" class="form-label">Tag to apply</label>
                <select name="tag" id="bulk-tag" class="form-select">
                    <option value="">Choose tag</option>
                    @foreach($tagBreakdown as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3" data-action-field="email" style="display:none;">
                <label for="bulk-subject" class="form-label">Email subject</label>
                <input type="text" name="subject" id="bulk-subject" class="form-control" placeholder="Subject">
            </div>
            <div class="col-md-3" data-action-field="email" style="display:none;">
                <label for="bulk-body" class="form-label">Email message</label>
                <textarea name="body" id="bulk-body" class="form-control" rows="2" placeholder="Message"></textarea>
            </div>
            <div class="col-md-3" data-action-field="sms" style="display:none;">
                <label for="bulk-sms" class="form-label">SMS message</label>
                <textarea name="sms_body" id="bulk-sms" class="form-control" rows="2" placeholder="Text message"></textarea>
            </div>
            <div class="col-md-2 d-grid ms-auto">
                <button type="submit" class="btn btn-secondary">Apply</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" style="width:40px;">
                            <input type="checkbox" id="select-all" class="form-check-input">
                        </th>
                        <th scope="col">Contact</th>
                        <th scope="col">Type</th>
                        <th scope="col">Company</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Groups</th>
                        <th scope="col">Tags</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $contact)
                        <tr>
                            <td>
                                <input type="checkbox" name="contacts[]" value="{{ $contact->id }}" class="form-check-input contact-checkbox">
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $contact->name }}</div>
                                <div class="text-muted small">
                                    {{ $contact->email ?? 'No email on file' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark text-capitalize">{{ $contact->type }}</span>
                            </td>
                            <td>{{ $contact->company ?? '—' }}</td>
                            <td>{{ $contact->phone ?? '—' }}</td>
                            <td>
                                @forelse($contact->groups as $group)
                                    <span class="badge bg-info text-dark me-1">{{ $group->name }}</span>
                                @empty
                                    <span class="text-muted small">None</span>
                                @endforelse
                            </td>
                            <td>
                                @forelse($contact->tags as $tag)
                                    <span class="badge bg-secondary me-1">{{ $tag->name }}</span>
                                @empty
                                    <span class="text-muted small">None</span>
                                @endforelse
                            </td>
                            <td class="text-end">
                                <a href="{{ route('contacts.show', $contact) }}" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">No contacts match the current filters.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0">
        {{ $contacts->links() }}
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.contact-checkbox');
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = selectAll.checked;
                });
            });
        }

        const actionSelect = document.getElementById('bulk-action');
        const conditionalFields = document.querySelectorAll('[data-action-field]');
        const toggleFields = function (value) {
            conditionalFields.forEach(function (field) {
                const actions = field.getAttribute('data-action-field').split(',');
                field.style.display = actions.includes(value) ? '' : 'none';
            });
        };
        if (actionSelect) {
            toggleFields(actionSelect.value || '');
            actionSelect.addEventListener('change', function () {
                toggleFields(this.value || '');
            });
        }
    });
</script>
@endpush
