@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">{{ $inspection->exists ? 'Edit' : 'Create' }} Inspection</h1>
    <form method="POST" enctype="multipart/form-data" action="{{ $inspection->exists ? route('inspections.update', $inspection) : route('inspections.store') }}" class="space-y-4">
        @csrf
        @if($inspection->exists)
            @method('PUT')
        @endif
        @unless($inspection->exists)
        <div>
            <label class="block mb-1">Property ID</label>
            <input type="number" name="property_id" class="w-full border p-2" value="{{ old('property_id', $inspection->property_id) }}">
        </div>
        <div>
            <label class="block mb-1">Agent ID</label>
            <input type="number" name="agent_id" class="w-full border p-2" value="{{ old('agent_id', $inspection->agent_id) }}">
        </div>
        @endunless
        <div>
            <label class="block mb-1">Scheduled At</label>
            <input type="datetime-local" name="scheduled_at" class="w-full border p-2" value="{{ old('scheduled_at', optional($inspection->scheduled_at)->format('Y-m-d\TH:i')) }}">
        </div>
        <div>
            <label class="block mb-1">Status</label>
            <select name="status" class="w-full border p-2">
                <option value="pending" @selected(old('status', $inspection->status)=='pending')>Pending</option>
                <option value="completed" @selected(old('status', $inspection->status)=='completed')>Completed</option>
            </select>
        </div>
        <div id="items" class="space-y-4">
            @foreach($inspection->items ?? [] as $index => $item)
            <div class="flex flex-col md:flex-row md:space-x-2">
                <input type="text" name="items[{{ $index }}][description]" class="flex-1 border p-2 mb-2 md:mb-0" placeholder="Description" value="{{ $item->description }}">
                <input type="file" name="items[{{ $index }}][photo]" class="flex-1 border p-2 mb-2 md:mb-0">
                <select name="items[{{ $index }}][status]" class="border p-2">
                    <option value="pending" @selected($item->status=='pending')>Pending</option>
                    <option value="done" @selected($item->status=='done')>Done</option>
                </select>
            </div>
            @endforeach
        </div>
        <button type="button" id="add-item" class="px-4 py-2 bg-gray-200 rounded">Add Item</button>
        <div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
        </div>
    </form>
</div>
<script>
    document.getElementById('add-item').addEventListener('click', function () {
        const container = document.getElementById('items');
        const index = container.children.length;
        const template = `
            <div class="flex flex-col md:flex-row md:space-x-2 mt-2">
                <input type="text" name="items[${index}][description]" class="flex-1 border p-2 mb-2 md:mb-0" placeholder="Description">
                <input type="file" name="items[${index}][photo]" class="flex-1 border p-2 mb-2 md:mb-0">
                <select name="items[${index}][status]" class="border p-2">
                    <option value="pending">Pending</option>
                    <option value="done">Done</option>
                </select>
            </div>`;
        container.insertAdjacentHTML('beforeend', template);
    });
</script>
@endsection
