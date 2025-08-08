@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Identity Verification</h1>
    <p>To continue, please have the following documents ready:</p>
    <ul>
        <li>Government issued photo ID</li>
        <li>Proof of address</li>
    </ul>
    @if(isset($verificationUrl))
        <a href="{{ $verificationUrl }}" class="btn btn-primary">Begin Verification</a>
    @endif
</div>
@endsection
