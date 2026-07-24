@extends('layouts.app')
@section('title', 'PDRM Officer Dashboard')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>PDRM Officer</span>
</nav>
@endsection

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-semibold text-gray-900">PDRM Officer Dashboard</h1>
    <p class="text-sm text-gray-500 mt-1">Case management, investigations, and alert monitoring.</p>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-icon bg-red-50 text-red-700">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <div class="stat-value">{{ $stats['activeMissingPersons'] }}</div>
        <div class="stat-label">Active Cases</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-yellow-50 text-yellow-700">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="stat-value">{{ $stats['pendingVerifications'] }}</div>
        <div class="stat-label">Pending Verification</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-blue-50 text-blue-700">
            <i class="fa-solid fa-bell"></i>
        </div>
        <div class="stat-value">{{ $stats['todayAlerts'] }}</div>
        <div class="stat-label">Today's Alerts</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-teal-50 text-teal-700">
            <i class="fa-solid fa-camera-retro"></i>
        </div>
        <div class="stat-value">{{ $stats['newDetections'] }}</div>
        <div class="stat-label">New Detections</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-red-50 text-red-700">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="stat-value">{{ $stats['highPriorityAlerts'] }}</div>
        <div class="stat-label">High Priority</div>
    </div>
</div>

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
                        <th>Detection ID</th>
                        <th>Match</th>
                        <th>Camera</th>
                        <th>Score</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentDetections as $detection)
                    <tr>
                        <td>
                            <a href="{{ route('detections.show', $detection) }}" class="text-navy hover:underline font-mono text-xs">
                                {{ $detection->detection_id }}
                            </a>
                        </td>
                        <td class="text-sm">{{ $detection->referenceName() }}</td>
                        <td class="text-sm">{{ $detection->camera->name ?? 'N/A' }}</td>
                        <td>
                            <span class="text-sm {{ $detection->similarity_score >= 80 ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                {{ number_format($detection->similarity_score, 1) }}%
                            </span>
                        </td>
                        <td><span class="badge {{ $detection->status->badge() }}">{{ $detection->status->label() }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-gray-400 py-6">No detections</td></tr>
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
                        <th>Alert ID</th>
                        <th>Subject</th>
                        <th>Priority</th>
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
                        <td class="text-sm">{{ $alert->referenceName() }}</td>
                        <td><span class="badge {{ $alert->priority->badge() }}">{{ $alert->priority->label() }}</span></td>
                        <td><span class="badge {{ $alert->status->badge() }}">{{ $alert->status->label() }}</span></td>
                        <td class="text-gray-500 text-xs">{{ $alert->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-gray-400 py-6">No alerts</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Pending Case Verifications --}}
<div class="card mt-6">
    <div class="card-header">
        <h2 class="text-sm font-semibold text-gray-900">Pending Case Verifications</h2>
        <a href="{{ route('missing-persons.index', ['status' => 'pending']) }}" class="text-xs text-navy hover:underline">View all</a>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Case Number</th>
                    <th>Name</th>
                    <th>Submitted By</th>
                    <th>Last Seen</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingVerifications as $case)
                <tr>
                    <td><code class="text-xs font-mono text-navy">{{ $case->case_number }}</code></td>
                    <td class="text-sm font-medium">{{ $case->full_name }}</td>
                    <td class="text-sm">{{ $case->creator->name ?? 'N/A' }}</td>
                    <td class="text-sm text-gray-500">{{ $case->last_seen_date?->format('d M Y') }}</td>
                    <td><span class="badge {{ $case->status->badge() }}">{{ $case->status->label() }}</span></td>
                    <td>
                        <a href="{{ route('missing-persons.show', $case) }}" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-eye"></i> Review
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-gray-400 py-6">No pending verification requests</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Quick Actions --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6">
    <a href="{{ route('missing-persons.create') }}" class="card p-4 hover:bg-gray-50 transition-colors text-center">
        <i class="fa-solid fa-plus-circle text-2xl text-navy mb-2"></i>
        <p class="text-sm font-medium text-gray-700">Add Missing Person</p>
    </a>
    <a href="{{ route('missing-persons.index', ['status' => 'pending']) }}" class="card p-4 hover:bg-gray-50 transition-colors text-center">
        <i class="fa-solid fa-clipboard-check text-2xl text-navy mb-2"></i>
        <p class="text-sm font-medium text-gray-700">Pending Verifications</p>
    </a>
    <a href="{{ route('detections.index') }}" class="card p-4 hover:bg-gray-50 transition-colors text-center">
        <i class="fa-solid fa-magnifying-glass text-2xl text-navy mb-2"></i>
        <p class="text-sm font-medium text-gray-700">Review Detections</p>
    </a>
    <a href="{{ route('alerts.index') }}" class="card p-4 hover:bg-gray-50 transition-colors text-center">
        <i class="fa-solid fa-bell text-2xl text-navy mb-2"></i>
        <p class="text-sm font-medium text-gray-700">View Alerts</p>
    </a>
</div>
@endsection
