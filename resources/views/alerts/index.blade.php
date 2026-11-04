@extends('layouts.app')
@section('title', 'Alerts')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>Alerts</span></nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div><h1 class="text-xl font-semibold text-gray-900">Alerts</h1><p class="text-sm text-gray-500 mt-1">Monitor camera detections and confirmed public sightings.</p></div>
</div>

<div class="filter-bar">
    <form method="GET" action="{{ route('alerts.index') }}" class="flex gap-2 flex-wrap flex-1">
        <input type="search" name="search" placeholder="Search alert ID..." class="form-input" value="{{ request('search') }}">
        <select name="status" class="form-select w-auto">
            <option value="">All Statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
    </form>
</div>

<div class="table-container">
    <table class="data-table">
        <thead><tr><th>Alert ID</th><th>Subject</th><th>Camera</th><th>Score</th><th>Status</th><th>Assigned To</th><th>Date</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
            @forelse($alerts as $alert)
            <tr>
                <td><a href="{{ route('alerts.show', $alert) }}" class="text-navy hover:underline font-mono text-xs">{{ $alert->alert_id }}</a></td>
                <td class="text-sm font-medium">{{ $alert->referenceName() }}</td>
                <td class="text-sm">{{ $alert->camera->name ?? $alert->sighting?->location ?? 'N/A' }}</td>
                <td><span class="text-sm {{ $alert->similarity_score >= 80 ? 'text-red-600 font-semibold' : 'text-gray-600' }}">{{ $alert->detection ? number_format($alert->similarity_score, 1).'%' : 'Witness confirmed' }}</span></td>
                <td><span class="badge {{ $alert->status->badge() }}">{{ $alert->status->label() }}</span></td>
                <td class="text-sm text-gray-500">{{ $alert->assignedTo->name ?? '—' }}</td>
                <td class="text-gray-500 text-xs">{{ $alert->created_at->format('d M Y') }}</td>
                <td class="text-right"><div class="flex items-center justify-end gap-1">
                    <a href="{{ route('alerts.show', $alert) }}" class="btn btn-sm btn-secondary"><i class="fa-solid fa-eye"></i></a>
                    <a href="{{ route('alerts.review', $alert) }}" class="btn btn-sm btn-primary"><i class="fa-solid fa-clipboard-check"></i></a>
                </div></td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-gray-400 py-8">
                    <i class="fa-solid fa-bell text-2xl mb-2 block"></i>
                    No alerts recorded yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($alerts->hasPages())
    <div class="pagination-container">
        <p class="text-sm text-gray-500">Showing {{ $alerts->firstItem() }} to {{ $alerts->lastItem() }} of {{ $alerts->total() }} entries</p>
        <div class="flex items-center gap-1">{{ $alerts->links() }}</div>
    </div>
    @endif
</div>
@endsection
