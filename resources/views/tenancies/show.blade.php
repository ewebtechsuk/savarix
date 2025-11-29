@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Tenancy Details</h1>

    <h5>Documents</h5>
    <form action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data" class="mb-3">
        @csrf
        <input type="hidden" name="documentable_type" value="App\\Models\\SavarixTenancy">
        <input type="hidden" name="documentable_id" value="{{ $tenancy->id }}">
        <div class="input-group">
            <input type="file" name="file" class="form-control" required>
            <button class="btn btn-primary">Upload</button>
        </div>
    </form>
    <ul class="list-group">
        @forelse($tenancy->documents as $document)
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
@endsection
