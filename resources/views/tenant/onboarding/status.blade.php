@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Verification Status</h1>
    @if($verification)
        <p>Current status: {{ ucfirst($verification->status) }}</p>
        <p>Provider: {{ $verification->provider }}</p>
    @else
        <p>No verification has been started.</p>
    @endif
</div>
@endsection
