@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-4">Request Maintenance</h1>
<form method="POST" action="{{ route('maintenance.store') }}" class="space-y-4">
    @csrf
    <div>
        <label class="block font-medium" for="property_id">Property ID</label>
        <input type="number" name="property_id" id="property_id" class="border rounded w-full" required>
    </div>
    <div>
        <label class="block font-medium" for="description">Description</label>
        <textarea name="description" id="description" class="border rounded w-full" required></textarea>
    </div>
    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Submit</button>
</form>
@endsection
