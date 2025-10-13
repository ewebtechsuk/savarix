@csrf
@php
    $selectedGroups = old('groups', isset($contact) ? $contact->groups->pluck('id')->all() : []);
    $selectedTags = old('tags', isset($contact) ? $contact->tags->pluck('id')->all() : []);
@endphp
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label for="type" class="form-label">Contact type<span class="text-danger">*</span></label>
        <select name="type" id="type" class="form-select" required>
            @foreach($types as $typeKey => $typeLabel)
                <option value="{{ $typeKey }}" @selected(old('type', $contact->type ?? 'landlord') === $typeKey)>{{ $typeLabel }}</option>
            @endforeach
        </select>
        @error('type')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="first_name" class="form-label">First name</label>
        <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name', $contact->first_name ?? '') }}" placeholder="Jane">
        @error('first_name')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="last_name" class="form-label">Last name</label>
        <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name', $contact->last_name ?? '') }}" placeholder="Doe">
        @error('last_name')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label for="name" class="form-label">Display name<span class="text-danger">*</span></label>
        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $contact->name ?? '') }}" placeholder="Primary display name" required>
        <div class="form-text">Used on dashboards and correspondence. Leave blank to auto-fill from first/last name.</div>
        @error('name')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="company" class="form-label">Company</label>
        <input type="text" name="company" id="company" class="form-control" value="{{ old('company', $contact->company ?? '') }}" placeholder="Organisation or trading name">
        @error('company')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $contact->email ?? '') }}" placeholder="name@example.com">
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="phone" class="form-label">Phone</label>
        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $contact->phone ?? '') }}" placeholder="+44 1234 567890">
        @error('phone')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="mb-4">
    <label for="address" class="form-label">Postal address</label>
    <textarea name="address" id="address" class="form-control" rows="2" placeholder="House number, street, city, postcode">{{ old('address', $contact->address ?? '') }}</textarea>
    @error('address')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
<div class="mb-4">
    <label for="notes" class="form-label">Internal notes</label>
    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Compliance, marketing preferences or other context">{{ old('notes', $contact->notes ?? '') }}</textarea>
    @error('notes')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label for="groups" class="form-label">Groups</label>
        <select name="groups[]" id="groups" class="form-select" multiple size="4">
            @foreach($allGroups as $group)
                <option value="{{ $group->id }}" @selected(in_array($group->id, $selectedGroups))>{{ $group->name }}</option>
            @endforeach
        </select>
        <div class="form-text">Assign the contact to marketing or compliance cohorts.</div>
    </div>
    <div class="col-md-6">
        <label for="tags" class="form-label">Tags</label>
        <select name="tags[]" id="tags" class="form-select" multiple size="4">
            @foreach($allTags as $tag)
                <option value="{{ $tag->id }}" @selected(in_array($tag->id, $selectedTags))>{{ $tag->name }}</option>
            @endforeach
        </select>
        <div class="form-text">Use tags to trigger workflows, lead nurture journeys or reporting segments.</div>
    </div>
</div>
