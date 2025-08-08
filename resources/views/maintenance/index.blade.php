@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-4">Maintenance Requests</h1>
<table class="min-w-full bg-white">
    <thead>
        <tr>
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Property</th>
            <th class="px-4 py-2">Tenant</th>
            <th class="px-4 py-2">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($requests as $request)
        <tr>
            <td class="border px-4 py-2"><a href="{{ route('maintenance.show', $request) }}">{{ $request->id }}</a></td>
            <td class="border px-4 py-2">{{ $request->property->title ?? 'N/A' }}</td>
            <td class="border px-4 py-2">{{ $request->tenant->id ?? 'N/A' }}</td>
            <td class="border px-4 py-2">{{ $request->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
