@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Verification Status</h1>
    @if($verification)
        <p>Current status: {{ ucfirst($verification->status) }}</p>
        <p>Provider: {{ ucfirst($verification->provider ?? 'unknown') }}</p>

        @if($verification->error_message)
            <div class="alert alert-danger" role="alert">
                {{ $verification->error_message }}
            </div>
        @endif

        @if($verification->provider_session_url && in_array($verification->status, ['started', 'pending']))
            <a href="{{ $verification->provider_session_url }}" class="btn btn-primary">Resume Verification</a>
        @endif
    @else
        <p>No verification has been started.</p>
    @endif
</div>
@endsection
