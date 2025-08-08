@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Inspections</h1>
    <table class="min-w-full bg-white">
        <thead>
            <tr>
                <th class="px-4 py-2">Property</th>
                <th class="px-4 py-2">Scheduled At</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($inspections as $inspection)
            <tr class="border-t">
                <td class="px-4 py-2">{{ $inspection->property->title ?? 'N/A' }}</td>
                <td class="px-4 py-2">{{ $inspection->scheduled_at->format('Y-m-d H:i') }}</td>
                <td class="px-4 py-2">{{ $inspection->status }}</td>
                <td class="px-4 py-2"><a href="{{ route('inspections.edit', $inspection) }}" class="text-blue-600">Edit</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
