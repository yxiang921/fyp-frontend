@extends('layouts.app')
@section('title', 'Missing Person: ' . $missingPerson->full_name)

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <a href="{{ route('missing-persons.index') }}">Missing Persons</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>{{ $missingPerson->case_number }}</span>
</nav>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success mb-4">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger mb-4">
    <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
</div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">{{ $missingPerson->full_name }}</h1>
        <p class="text-sm text-gray-500 mt-1">Case Number: <code class="font-mono">{{ $missingPerson->case_number }}</code></p>
    </div>
    <div class="flex gap-2">
        {{-- Verify button for PDRM officers when case is pending --}}
        @if($missingPerson->status->value === 'pending' && in_array(auth()->user()->role->value, ['super_admin', 'pdrm_officer']))
        <form method="POST" action="{{ route('missing-persons.verify', $missingPerson) }}">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-check-circle"></i> Verify & Activate
            </button>
        </form>
        @endif
        <a href="{{ route('missing-persons.edit', $missingPerson) }}" class="btn btn-secondary">
            <i class="fa-solid fa-pen-to-square"></i> Edit
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left Column — Details --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-gray-900">Personal Information</h2>
                <span class="badge {{ $missingPerson->status->badge() }}">{{ $missingPerson->status->label() }}</span>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Full Name</span><span class="detail-value font-medium">{{ $missingPerson->full_name }}</span></div>
                    <div class="detail-item"><span class="detail-label">Gender</span><span class="detail-value capitalize">{{ $missingPerson->gender }}</span></div>
                    <div class="detail-item"><span class="detail-label">Date of Birth</span>
                        <span class="detail-value">
                            @if($missingPerson->date_of_birth)
                                {{ $missingPerson->date_of_birth->format('d M Y') }} ({{ $missingPerson->date_of_birth->age }} years)
                            @else
                                <span class="text-gray-400">Not provided</span>
                            @endif
                        </span>
                    </div>
                    <div class="detail-item"><span class="detail-label">National ID</span>
                        <span class="detail-value font-mono">{{ $missingPerson->national_id ?? 'Not provided' }}</span>
                    </div>
                    <div class="detail-item"><span class="detail-label">Last Seen Date</span>
                        <span class="detail-value font-medium text-red-600">{{ $missingPerson->last_seen_date?->format('d M Y') }}</span>
                    </div>
                    <div class="detail-item"><span class="detail-label">Last Seen Location</span><span class="detail-value">{{ $missingPerson->last_seen_location }}</span></div>
                    <div class="detail-item"><span class="detail-label">Date Filed</span><span class="detail-value">{{ $missingPerson->created_at->format('d M Y, H:i') }}</span></div>
                    <div class="detail-item"><span class="detail-label">Filed By</span><span class="detail-value">{{ $missingPerson->creator?->name ?? 'Unknown' }}</span></div>
                    <div class="detail-item"><span class="detail-label">Created By Role</span><span class="detail-value">{{ $missingPerson->creator?->role?->label() ?? 'N/A' }}</span></div>
                </div>

                @if($missingPerson->description)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Description</h3>
                    <p class="text-sm text-gray-700">{{ $missingPerson->description }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Photo Gallery --}}
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-gray-900">Reference Photos</h2>
                <span class="text-xs text-gray-400">{{ $missingPerson->photos->count() }} photo(s)</span>
            </div>
            <div class="card-body">
                @if($missingPerson->photos->count() > 0)
                <div class="photo-grid">
                    @foreach($missingPerson->photos as $photo)
                    <div class="photo-thumb">
                        <img src="{{ $photo->url() }}" alt="Photo of {{ $missingPerson->full_name }}">
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-6 text-gray-400">
                    <i class="fa-solid fa-image text-3xl mb-2 block"></i>
                    <p class="text-sm">No photos uploaded</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Related Alerts --}}
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-gray-900">Related Alerts</h2>
                <a href="{{ route('alerts.index') }}" class="text-xs text-navy hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Alert ID</th><th>Camera</th><th>Score</th><th>Priority</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse($missingPerson->alerts()->latest()->take(10)->get() as $alert)
                        <tr>
                            <td><a href="{{ route('alerts.show', $alert) }}" class="text-navy hover:underline font-mono text-xs">{{ $alert->alert_id }}</a></td>
                            <td class="text-sm">{{ $alert->camera?->name ?? 'N/A' }}</td>
                            <td>
                                <span class="text-sm {{ $alert->similarity_score >= 80 ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                    {{ number_format($alert->similarity_score, 1) }}%
                                </span>
                            </td>
                            <td><span class="badge {{ $alert->priority->badge() }}">{{ $alert->priority->label() }}</span></td>
                            <td><span class="badge {{ $alert->status->badge() }}">{{ $alert->status->label() }}</span></td>
                            <td class="text-gray-500 text-xs">{{ $alert->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-gray-400 py-4">No alerts for this case</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-gray-900">Case Timeline</h2>
            </div>
            <div class="card-body">
                @if($missingPerson->timeline()->latest()->count() > 0)
                <div class="timeline">
                    @foreach($missingPerson->timeline()->latest()->get() as $event)
                    <div class="timeline-item">
                        <div class="timeline-dot {{ match($event->event_type) { 'case_created' => 'blue', 'verified' => 'green', 'status_changed' => 'yellow', default => 'blue' } }}"></div>
                        <div class="ml-2">
                            <p class="text-xs text-gray-400">{{ $event->created_at->format('d M Y H:i') }}</p>
                            <p class="text-sm font-medium text-gray-900">{{ str_replace('_', ' ', ucfirst($event->event_type)) }}</p>
                            <p class="text-sm text-gray-500">{{ $event->description }}</p>
                            @if($event->recordedBy)
                            <p class="text-xs text-gray-400 mt-0.5">by {{ $event->recordedBy->name }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-400 text-center py-4">No timeline events yet.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Right Column — Quick Info --}}
    <div class="space-y-6">
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Detection Statistics</h2></div>
            <div class="card-body space-y-3">
                <div class="flex justify-between"><span class="text-sm text-gray-500">Total Detections</span><span class="font-semibold">{{ $detectionStats['total'] }}</span></div>
                <div class="flex justify-between"><span class="text-sm text-gray-500">High Confidence (≥80%)</span><span class="font-semibold text-red-600">{{ $detectionStats['highConfidence'] }}</span></div>
                <div class="flex justify-between"><span class="text-sm text-gray-500">Alerts Generated</span><span class="font-semibold">{{ $detectionStats['alertsGenerated'] }}</span></div>
                <div class="flex justify-between"><span class="text-sm text-gray-500">Pending Review</span><span class="font-semibold text-yellow-600">{{ $detectionStats['pendingVerification'] }}</span></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Cameras Detected At</h2></div>
            <div class="card-body space-y-3">
                @if($detectedCameras->count() > 0)
                    @foreach($detectedCameras as $cam)
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-video text-gray-400 text-xs"></i>
                        <span class="text-sm text-gray-700">{{ $cam }}</span>
                    </div>
                    @endforeach
                @else
                    <p class="text-sm text-gray-400">No detections yet.</p>
                @endif
            </div>
        </div>

        <div class="disclaimer">
            <i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i>
            <p>This case is {{ $missingPerson->status->label() }}. All facial recognition matches are potential leads only and require human verification before any enforcement action.</p>
        </div>
    </div>
</div>
@endsection
