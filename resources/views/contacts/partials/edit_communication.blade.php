@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h3>Edit Communication</h3>
    <form action="{{ route('contacts.communications.update', [$contact, $comm]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="communication" class="form-label">Communication</label>
            <input type="text" name="communication" id="communication" class="form-control" value="{{ old('communication', $comm->communication) }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('contacts.show', $contact) }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
