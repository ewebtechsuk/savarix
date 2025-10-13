@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark fw-semibold">Edit contact</div>
            <div class="card-body">
                <p class="text-muted">Update contact preferences, segmentation and contact details.</p>
                <form method="POST" action="{{ route('contacts.update', $contact) }}">
                    @csrf
                    @method('PUT')
                    @include('contacts.partials.form')
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="text-muted small">Last updated {{ optional($contact->updated_at)->diffForHumans() }}</div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('contacts.show', $contact) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-warning text-dark">Save changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
