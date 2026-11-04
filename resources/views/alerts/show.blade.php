@extends('layouts.app')
@section('title', 'Alert Detail')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><a href="{{ route('alerts.index') }}">Alerts</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>{{ $alert->alert_id }}</span></nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div><h1 class="text-xl font-semibold text-gray-900">Alert: {{ $alert->alert_id }}</h1><p class="text-sm text-gray-500 mt-1">{{ $alert->sighting ? 'Alert generated from a confirmed public sighting.' : 'Alert generated from facial recognition detection.' }}</p></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="card"><div class="card-header flex items-center justify-between"><h2 class="text-sm font-semibold text-gray-900">Alert Information</h2><span class="badge {{ $alert->status->badge() }}">{{ $alert->status->label() }}</span></div>
        <div class="card-body"><div class="detail-grid">
            <div class="detail-item"><span class="detail-label">Alert ID</span><span class="detail-value font-mono">{{ $alert->alert_id }}</span></div>
            <div class="detail-item"><span class="detail-label">Subject</span><span class="detail-value font-medium">{{ $alert->referenceName() }}</span></div>

            <div class="detail-item"><span class="detail-label">Status</span><span class="detail-value"><span class="badge {{ $alert->status->badge() }}">{{ $alert->status->label() }}</span></span></div>
            <div class="detail-item"><span class="detail-label">Evidence type</span><span class="detail-value font-medium">{{ $alert->sighting ? 'Confirmed public sighting' : 'Camera face match' }}</span></div>
            <div class="detail-item"><span class="detail-label">{{ $alert->sighting ? 'Reported location' : 'Camera' }}</span><span class="detail-value">{{ $alert->sighting?->location ?? $alert->camera?->name ?? 'N/A' }}</span></div>
            <div class="detail-item"><span class="detail-label">Assigned To</span><span class="detail-value">{{ $alert->assignedTo->name ?? '—' }}</span></div>
            <div class="detail-item"><span class="detail-label">Source</span><span class="detail-value">@if($alert->detection)<a href="{{ route('detections.show', $alert->detection) }}" class="text-navy hover:underline font-mono text-xs">{{ $alert->detection->detection_id }}</a>@elseif($alert->sighting)<a href="{{ route('sightings.show', $alert->sighting) }}" class="text-navy hover:underline">Sighting #{{ $alert->sighting->id }}</a>@else N/A @endif</span></div>
            <div class="detail-item"><span class="detail-label">Created</span><span class="detail-value">{{ $alert->created_at->format('d M Y, h:i A') }}</span></div>
        </div>
        @if($alert->description)
        <div class="mt-4 pt-4 border-t border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Description</h3>
            <p class="text-sm text-gray-700">{{ $alert->description }}</p>
        </div>
        @endif
        </div></div>

        {{-- Timeline --}}
        @if($alert->timeline->isNotEmpty())
        <div class="card"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Alert Timeline</h2></div>
        <div class="card-body"><div class="timeline">
            @foreach($alert->timeline as $event)
            <div class="timeline-item"><div class="timeline-dot blue"></div><div class="ml-2"><p class="text-xs text-gray-400">{{ $event->created_at->format('d M Y, H:i') }}</p><p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $event->event_type)) }}</p><p class="text-sm text-gray-500">{{ $event->description }}</p></div></div>
            @endforeach
        </div></div></div>
        @endif
    </div>

    <div class="space-y-6">
        <div class="card"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Actions</h2></div>
        <div class="card-body space-y-2">
            @if($alert->detection)
            <a href="{{ route('detections.show', $alert->detection) }}" class="btn btn-primary w-full"><i class="fa-solid fa-video"></i> View Detection</a>
            @endif
            @if($alert->missingPerson)
            <a href="{{ route('missing-persons.show', $alert->missingPerson) }}" class="btn btn-primary w-full"><i class="fa-solid fa-folder-open"></i> View Case</a>
            @endif
            @if($alert->sighting)<a href="{{ route('sightings.show', $alert->sighting) }}" class="btn btn-secondary w-full"><i class="fa-solid fa-location-dot"></i> View Sighting</a>@endif
            <form action="{{ route('alerts.send-alert', $alert) }}" method="POST" onsubmit="return confirm('Send alert notification to patrol units in the related area?')">
                @csrf
                <button type="submit" class="btn btn-warning w-full"><i class="fa-solid fa-paper-plane"></i> Send Alert</button>
            </form>
        </div></div>

        {{-- Location Map --}}
        @if(($alert->camera && $alert->camera->latitude !== null && $alert->camera->longitude !== null) || ($alert->sighting && $alert->sighting->latitude !== null && $alert->sighting->longitude !== null))
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-900"><i class="fa-solid fa-map-pin text-navy mr-1"></i> Detection Location</h2></div>
            <div class="card-body p-0">
                <div id="location-map" class="map-container" style="height: 240px;"></div>
            </div>
            <div class="card-footer text-xs text-gray-500">
                {{ $alert->sighting?->location ?? ($alert->camera->name.' — '.($alert->camera->location ?? 'N/A')) }}<br>
            </div>
        </div>
        @endif

        {{-- Status Change --}}
        <div class="card"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Update Status</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('alerts.update-status', $alert) }}">
                @csrf
                @method('PATCH')
                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        @foreach(\App\Enums\AlertStatus::cases() as $s)
                            <option value="{{ $s->value }}" {{ $alert->status === $s ? 'selected' : '' }}>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="status_notes" class="form-label">Notes</label>
                    <textarea id="status_notes" name="notes" rows="3" class="form-textarea" placeholder="Reason for status change..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-full"><i class="fa-solid fa-arrow-right-arrow-left"></i> Update Status</button>
            </form>
        </div></div>
        <div class="disclaimer"><i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i><p>Alert based on automated facial recognition. All identity matches require human verification. Do not take enforcement action based solely on this alert.</p></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var mapEl = document.getElementById('location-map');
    if (!mapEl) return;

    var lat = {{ $alert->sighting?->latitude ?? $alert->camera?->latitude ?? 4.2105 }};
    var lng = {{ $alert->sighting?->longitude ?? $alert->camera?->longitude ?? 108.9758 }};
    var camName = @json($alert->sighting ? 'Confirmed public sighting' : ($alert->camera->name ?? 'N/A'));
    var camLocation = @json($alert->sighting?->location ?? $alert->camera?->location ?? 'N/A');

    var map = L.map('location-map').setView([lat, lng], 15);
    L.tileLayer(@json(config('maps.tile_url')), {
        attribution: @json(config('maps.attribution')),
        maxZoom: @json(config('maps.max_zoom')),
    }).addTo(map);

    var marker = L.marker([lat, lng]).addTo(map);
    marker.bindPopup(
        '<strong>' + camName + '</strong><br>' +
        'Location: ' + camLocation + '<br>' +
        '<span style="font-size:11px;color:#888;">' + lat.toFixed(4) + ', ' + lng.toFixed(4) + '</span>'
    ).openPopup();
});
</script>
@endpush
