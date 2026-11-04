@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>Admin Overview</span>
</nav>
@endsection

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-semibold text-gray-900">Admin Dashboard</h1>
    <p class="text-sm text-gray-500 mt-1">System overview and administrative controls.</p>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-icon bg-blue-50 text-blue-700">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-value">{{ $stats['totalUsers'] }}</div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-red-50 text-red-700">
            <i class="fa-solid fa-bell"></i>
        </div>
        <div class="stat-value">{{ $stats['totalAlerts'] }}</div>
        <div class="stat-label">Total Alerts</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green-50 text-green-700">
            <i class="fa-solid fa-video"></i>
        </div>
        <div class="stat-value">{{ $stats['totalCameras'] }}</div>
        <div class="stat-label">Cameras ({{ $stats['onlineCameras'] }} online)</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-amber-50 text-amber-700">
            <i class="fa-solid fa-folder-open"></i>
        </div>
        <div class="stat-value">{{ $stats['activeCases'] }}</div>
        <div class="stat-label">Active Cases</div>
    </div>
</div>

{{-- Second Row Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">Pending Verifications</span>
            <span class="badge badge-yellow">{{ $stats['pendingVerifications'] }}</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">New Alerts</span>
            <span class="badge badge-blue">{{ $stats['newAlerts'] }}</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">Online Cameras</span>
            <span class="badge badge-green">{{ $stats['onlineCameras'] }}</span>
        </div>
    </div>
</div>

{{-- Recent Activity --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Recent Detections --}}
    <div class="card">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-gray-900">Recent Detections</h2>
            <a href="{{ route('detections.index') }}" class="text-xs text-navy hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Camera</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentDetections as $detection)
                    <tr>
                        <td><code class="text-xs font-mono">{{ $detection->detection_id }}</code></td>
                        <td>{{ $detection->camera->name ?? 'N/A' }}</td>
                        <td>
                            <span class="{{ $detection->similarity_score >= 80 ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                {{ number_format($detection->similarity_score, 1) }}%
                            </span>
                        </td>
                        <td><span class="badge {{ $detection->status->badge() }}">{{ $detection->status->label() }}</span></td>
                        <td class="text-gray-500">{{ $detection->detected_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-gray-400 py-6">No detections recorded</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Alerts --}}
    <div class="card">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-gray-900">Recent Alerts</h2>
            <a href="{{ route('alerts.index') }}" class="text-xs text-navy hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAlerts as $alert)
                    <tr>
                        <td>
                            <a href="{{ route('alerts.show', $alert) }}" class="text-navy hover:underline font-mono text-xs">
                                {{ $alert->alert_id }}
                            </a>
                        </td>
                        <td><span class="badge {{ $alert->status->badge() }}">{{ $alert->status->label() }}</span></td>
                        <td class="text-gray-500 text-xs">{{ $alert->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-gray-400 py-6">No alerts recorded</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Quick Links --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6">
    <a href="{{ route('users.index') }}" class="card p-4 hover:bg-gray-50 transition-colors text-center">
        <i class="fa-solid fa-users-gear text-2xl text-navy mb-2"></i>
        <p class="text-sm font-medium text-gray-700">User Management</p>
    </a>
    <a href="{{ route('missing-persons.index') }}" class="card p-4 hover:bg-gray-50 transition-colors text-center">
        <i class="fa-solid fa-magnifying-glass text-2xl text-navy mb-2"></i>
        <p class="text-sm font-medium text-gray-700">Missing Persons</p>
    </a>
    <a href="{{ route('cameras.index') }}" class="card p-4 hover:bg-gray-50 transition-colors text-center">
        <i class="fa-solid fa-video text-2xl text-navy mb-2"></i>
        <p class="text-sm font-medium text-gray-700">Cameras</p>
    </a>
    <a href="{{ route('audit-logs.index') }}" class="card p-4 hover:bg-gray-50 transition-colors text-center">
        <i class="fa-solid fa-clipboard-list text-2xl text-navy mb-2"></i>
        <p class="text-sm font-medium text-gray-700">Audit Logs</p>
    </a>
</div>
@endsection
