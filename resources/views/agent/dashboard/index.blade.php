@extends('agent.layouts.app')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow p-4">
            <div class="text-xs uppercase text-slate-500 mb-2">Pipeline</div>
            <div class="text-3xl font-semibold mb-1">{{ $openPropertiesCount }}</div>
            <div class="text-xs text-slate-500">Active instructions</div>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <div class="text-xs uppercase text-slate-500 mb-2">Viewings</div>
            <div class="text-3xl font-semibold mb-1">{{ $todayViewingsCount }}</div>
            <div class="text-xs text-slate-500">Today</div>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <div class="text-xs uppercase text-slate-500 mb-2">Tasks</div>
            <div class="text-3xl font-semibold mb-1">{{ $openTasksCount }}</div>
            <div class="text-xs text-slate-500">Open tasks</div>
        </div>
    </div>
@endsection
