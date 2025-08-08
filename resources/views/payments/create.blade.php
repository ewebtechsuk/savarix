@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="fw-bold mb-4">Pay Rent</h1>
    <form id="payment-form">
        @csrf
        <input type="hidden" id="amount" value="{{ $tenancy->rent }}">
        <div id="card-element" class="mb-3"></div>
        <button id="submit" class="btn btn-primary">Pay £{{ number_format($tenancy->rent, 2) }}</button>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('{{ config('services.stripe.key') }}');
const elements = stripe.elements();
const card = elements.create('card');
card.mount('#card-element');

const form = document.getElementById('payment-form');
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const amount = document.getElementById('amount').value;
    const response = await fetch('{{ route('payments.store', $tenancy) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ amount })
    });
    const data = await response.json();
    const {error} = await stripe.confirmCardPayment(data.client_secret, {
        payment_method: {
            card: card
        }
    });
    if (error) {
        alert(error.message);
    } else {
        window.location.href = '/';
    }
});
</script>
@endpush
