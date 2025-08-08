@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Workflow</h1>
    @include('workflows.form', ['workflow' => $workflow])
</div>
@endsection
