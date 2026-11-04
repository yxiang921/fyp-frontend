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
@if($missingPerson->duplicate_review)
<div class="card mb-4"><div class="card-body">
    <h2>Duplicate review</h2>
    <p>Face check: {{ $missingPerson->duplicate_review['face_status'] ?? 'not checked' }}. Similarity is a review lead, not identification.</p>
    @foreach($missingPerson->duplicate_review['matches'] ?? [] as $candidate)
        <p>{{ $candidate['case_number'] }} — {{ implode(', ', $candidate['reasons']) }}</p>
    @endforeach
</div></div>
@endif
@include('reports._case-map', ['points' => app(\App\Services\CaseMapService::class)->points($missingPerson), 'mapId' => 'case-activity-map'])
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
                    <div class="detail-item"><span class="detail-label">Ethnicity / Bangsa</span><span class="detail-value">{{ $missingPerson->ethnicity ?? 'Not provided' }}</span></div>
                    <div class="detail-item"><span class="detail-label">Nationality</span><span class="detail-value">{{ $missingPerson->nationality ?? 'Not provided' }}</span></div>
                    <div class="detail-item"><span class="detail-label">Height</span><span class="detail-value">{{ $missingPerson->height_cm ? $missingPerson->height_cm . ' cm' : 'Not provided' }}</span></div>
                    <div class="detail-item"><span class="detail-label">Weight</span><span class="detail-value">{{ $missingPerson->weight_kg ? rtrim(rtrim(number_format((float) $missingPerson->weight_kg, 2), '0'), '.') . ' kg' : 'Not provided' }}</span></div>
                    <div class="detail-item"><span class="detail-label">Hair</span><span class="detail-value">{{ collect([$missingPerson->hair_type, $missingPerson->hair_color])->filter()->join(', ') ?: 'Not provided' }}</span></div>
                    <div class="detail-item"><span class="detail-label">Eye Color</span><span class="detail-value">{{ $missingPerson->eye_color ?? 'Not provided' }}</span></div>
                    <div class="detail-item"><span class="detail-label">Last Seen Date</span>
                        <span class="detail-value font-medium text-red-600">{{ $missingPerson->last_seen_date?->format('d M Y') }}</span>
                    </div>
                    <div class="detail-item"><span class="detail-label">Last Seen Location</span><span class="detail-value">{{ $missingPerson->last_seen_location }}</span></div>
                    <div class="detail-item"><span class="detail-label">Date Filed</span><span class="detail-value">{{ $missingPerson->created_at->format('d M Y, H:i') }}</span></div>
                    <div class="detail-item">
                        <span class="detail-label">Filed By</span>
                        <span class="detail-value">
                            @if($missingPerson->created_by)
                                {{ $missingPerson->creator->name }} ({{ $missingPerson->creator->role->label() }})
                            @else
                                <span class="badge badge-gray text-xs">Public (Anonymous)</span>
                            @endif
                        </span>
                    </div>
                </div>

                @if($missingPerson->clothing_description || $missingPerson->identifying_features)
                <div class="mt-4 pt-4 border-t border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><h3 class="text-sm font-semibold text-gray-700 mb-1">Last-worn Clothing</h3><p class="text-sm text-gray-700">{{ $missingPerson->clothing_description ?: 'Not provided' }}</p></div>
                    <div><h3 class="text-sm font-semibold text-gray-700 mb-1">Distinguishing Features</h3><p class="text-sm text-gray-700">{{ $missingPerson->identifying_features ?: 'Not provided' }}</p></div>
                </div>
                @endif

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
                    <thead><tr><th>Alert ID</th><th>Camera</th><th>Score</th><th>Status</th><th>Date</th></tr></thead>
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
                            <td><span class="badge {{ $alert->status->badge() }}">{{ $alert->status->label() }}</span></td>
                            <td class="text-gray-500 text-xs">{{ $alert->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-gray-400 py-4">No alerts for this case</td></tr>
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
        {{-- Detection Map --}}
        @if($mapLocations->isNotEmpty())
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-900"><i class="fa-solid fa-map-pin text-navy mr-1"></i> Detection Locations</h2></div>
            <div class="card-body p-0">
                <div id="detection-map" class="map-container" style="height: 280px;"></div>
            </div>
        </div>
        @endif

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

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var mapEl = document.getElementById('detection-map');
    if (!mapEl) return;

    var map = L.map('detection-map').setView([4.2105, 108.9758], 5);
    L.tileLayer(@json(config('maps.tile_url')), {
        attribution: @json(config('maps.attribution')),
        maxZoom: @json(config('maps.max_zoom')),
    }).addTo(map);

    var locations = @json($mapLocations);
    if (locations.length > 0) {
        locations.forEach(function(loc) {
            var marker = L.circleMarker([loc.latitude, loc.longitude], {
                radius: 8,
                fillColor: '#ef4444',
                color: '#fff',
                weight: 2,
                fillOpacity: 0.9
            }).addTo(map);

            marker.bindPopup(
                '<strong>' + loc.name + '</strong><br>' +
                'Location: ' + (loc.location || 'N/A') + '<br>' +
                'Detected: ' + loc.detected_at + '<br>' +
                'Score: ' + loc.score.toFixed(1) + '%<br>' +
                '<span style="font-size:11px;color:#888;">' + loc.latitude.toFixed(4) + ', ' + loc.longitude.toFixed(4) + '</span>'
            );
        });

        var bounds = new L.LatLngBounds(locations.map(function(l) { return [l.latitude, l.longitude]; }));
        if (bounds.isValid()) {
            map.fitBounds(bounds, { padding: [20, 20], maxZoom: 15 });
        }
    }
});
</script>
@endpush
