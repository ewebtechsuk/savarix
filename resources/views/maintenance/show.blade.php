@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-4">Maintenance Request #{{ $request->id }}</h1>
<p><strong>Property:</strong> {{ $request->property->title ?? 'N/A' }}</p>
<p><strong>Tenant:</strong> {{ $request->tenant->id ?? 'N/A' }}</p>
<p class="mb-4"><strong>Description:</strong> {{ $request->description }}</p>
<form method="POST" action="{{ route('maintenance.update', $request) }}" class="space-y-4">
    @csrf
    @method('PUT')
    <div>
        <label class="block font-medium" for="status">Status</label>
        <select name="status" id="status" class="border rounded w-full">
            <option value="pending" @selected($request->status === 'pending')>Pending</option>
            <option value="in_progress" @selected($request->status === 'in_progress')>In Progress</option>
            <option value="completed" @selected($request->status === 'completed')>Completed</option>
        </select>
    </div>
    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
</form>
@endsection
