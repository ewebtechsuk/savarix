@extends('admin.dashboard')

@section('content')
<div class="container mx-auto p-4 max-w-lg">
    <h1 class="text-2xl font-bold mb-4">Record Manual Bank Transfer Payment</h1>
    <form action="{{ route('admin.companies.billing.manual.store', $tenant->id) }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block mb-1">Amount (in pence)</label>
            <input type="number" name="amount" class="w-full border rounded p-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Reference</label>
            <input type="text" name="reference" class="w-full border rounded p-2">
        </div>
        <div class="mb-4">
            <label class="block mb-1">Note</label>
            <textarea name="note" class="w-full border rounded p-2"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Record Payment</button>
    </form>
    <div class="mt-6 p-4 bg-gray-100 rounded">
        <h2 class="font-bold mb-2">Bank Transfer Details</h2>
        <p>Bank: Example Bank</p>
        <p>Account Name: Savirix Ltd</p>
        <p>Sort Code: 12-34-56</p>
        <p>Account Number: 12345678</p>
        <p>Reference: [Company Name or Invoice]</p>
    </div>
</div>
@endsection
