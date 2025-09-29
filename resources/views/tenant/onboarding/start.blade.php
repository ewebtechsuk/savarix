@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Identity Verification</h1>
    <p>To continue, please have the following documents ready:</p>
    <ul>
        <li>Government issued photo ID</li>
        <li>Proof of address</li>
    </ul>

    @if(isset($verification) && $verification->error_message)
        <div class="alert alert-danger" role="alert">
            {{ $verification->error_message }}
        </div>
    @endif

    @if(isset($verification) && $verification->provider_session_url)
        <p class="mb-3">
            Current status: <strong>{{ ucfirst($verification->status ?? 'pending') }}</strong>
        </p>
        <a href="{{ $verification->provider_session_url }}" class="btn btn-primary">Begin Verification</a>
    @else
        <p class="text-muted">We&apos;re preparing your verification session. Please refresh this page in a moment.</p>
    @endif
</div>
@endsection
