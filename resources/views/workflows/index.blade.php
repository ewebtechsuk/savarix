@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Workflows</h1>
    <a href="{{ route('workflows.create') }}" class="btn btn-primary">New Workflow</a>
    <ul>
        @foreach($workflows as $workflow)
            <li><a href="{{ route('workflows.edit', $workflow) }}">{{ $workflow->name }}</a></li>
        @endforeach
    </ul>
</div>
@endsection
