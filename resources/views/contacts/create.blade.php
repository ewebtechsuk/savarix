@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white fw-semibold">New contact</div>
            <div class="card-body">
                <p class="text-muted">Capture the key profile, communication details and segmentation preferences for this record.</p>
                <form method="POST" action="{{ route('contacts.store') }}">
                    @include('contacts.partials.form')
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save contact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
