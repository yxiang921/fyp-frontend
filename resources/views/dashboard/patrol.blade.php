@extends('layouts.app')
@section('title', 'Patrol Unit Dashboard')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>Patrol Unit</span>
</nav>
@endsection

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-semibold text-gray-900">Patrol Unit Dashboard</h1>
    <p class="text-sm text-gray-500 mt-1">Patrol areas: {{ $patrolAreas->pluck('name')->join(', ') ?: 'No area assigned — contact an administrator.' }}</p>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-icon bg-blue-50 text-blue-700">
            <i class="fa-solid fa-bell"></i>
        </div>
        <div class="stat-value">{{ $stats['assignedAlerts'] }}</div>
        <div class="stat-label">Total Assigned Alerts</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-red-50 text-red-700">
            <i class="fa-solid fa-exclamation-circle"></i>
        </div>
        <div class="stat-value">{{ $stats['newAlerts'] }}</div>
        <div class="stat-label">New Alerts</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green-50 text-green-700">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="stat-value">{{ $stats['resolvedToday'] }}</div>
        <div class="stat-label">Resolved Today</div>
    </div>
</div>

{{-- Assigned Alerts --}}
<div class="card">
    <div class="card-header">
        <h2 class="text-sm font-semibold text-gray-900">My Assigned Alerts</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Alert ID</th>
                    <th>Subject</th>
                    <th>Location</th>
                    <th>Similarity</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignedAlerts as $alert)
                <tr>
                    <td>
                        <a href="{{ route('alerts.show', $alert) }}" class="text-navy hover:underline font-mono text-xs">
                            {{ $alert->alert_id }}
                        </a>
                    </td>
                    <td class="text-sm font-medium">{{ $alert->referenceName() }}</td>
                    <td class="text-sm text-gray-500">{{ $alert->camera->name ?? 'N/A' }}</td>
                    <td>
                        <span class="text-sm {{ $alert->similarity_score >= 80 ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                            {{ number_format($alert->similarity_score, 1) }}%
                        </span>
                    </td>
                    <td><span class="badge {{ $alert->status->badge() }}">{{ $alert->status->label() }}</span></td>
                    <td class="text-xs text-gray-500">{{ $alert->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('alerts.show', $alert) }}" class="btn btn-sm btn-secondary">
                            <i class="fa-solid fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-gray-400 py-8">
                        <i class="fa-solid fa-check-circle text-2xl mb-2 block text-green-400"></i>
                        No assigned alerts. All clear.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Quick Info --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="card">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-gray-900">Field Action Guidelines</h2>
        </div>
        <div class="card-body space-y-3 text-sm text-gray-600">
            <div class="flex gap-2">
                <i class="fa-solid fa-1 text-navy mt-0.5"></i>
                <span>Review alert details and location before proceeding.</span>
            </div>
            <div class="flex gap-2">
                <i class="fa-solid fa-2 text-navy mt-0.5"></i>
                <span>Confirm identity match before taking action.</span>
            </div>
            <div class="flex gap-2">
                <i class="fa-solid fa-3 text-navy mt-0.5"></i>
                <span>Report back to the PDRM officer after field response.</span>
            </div>
            <div class="flex gap-2">
                <i class="fa-solid fa-4 text-navy mt-0.5"></i>
                <span>Facial recognition provides leads only — not proof of identity.</span>
            </div>
        </div>
    </div>

    <div class="disclaimer">
        <i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i>
        <div>
            <p class="font-medium">Important Notice</p>
            <p class="mt-0.5">Alerts are generated from facial recognition matches and are potential leads only. Always verify identity through official documentation before taking enforcement action.</p>
        </div>
    </div>
</div>
@endsection
