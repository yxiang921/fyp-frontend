@extends('layouts.app')
@section('title', 'Report & Analysis')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>Report & Analysis</span></nav>
@endsection

@section('content')
@include('reports._performance')
<div class="mb-6">
    <h1 class="text-xl font-semibold text-gray-900">Report & Analysis</h1>
    <p class="text-sm text-gray-500 mt-1">System-wide analytics and geographic overview of cameras, detections, and last-seen locations across Malaysia.</p>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-6">
    <div class="stat-card text-center">
        <div class="stat-value text-lg">{{ $stats['totalDetections'] }}</div>
        <div class="stat-label text-xs">Detections</div>
    </div>
    <div class="stat-card text-center">
        <div class="stat-value text-lg">{{ $stats['totalAlerts'] }}</div>
        <div class="stat-label text-xs">Alerts</div>
    </div>
    <div class="stat-card text-center">
        <div class="stat-value text-lg">{{ $stats['totalCameras'] }}</div>
        <div class="stat-label text-xs">Cameras</div>
    </div>
    <div class="stat-card text-center">
        <div class="stat-value text-lg">{{ $stats['activeCases'] }}</div>
        <div class="stat-label text-xs">Active Cases</div>
    </div>
    <div class="stat-card text-center">
        <div class="stat-value text-lg text-red-600">{{ $stats['highConfidenceDetections'] }}</div>
        <div class="stat-label text-xs">High Confidence</div>
    </div>
    <div class="stat-card text-center">
        <div class="stat-value text-lg text-blue-600">{{ $stats['newAlerts'] }}</div>
        <div class="stat-label text-xs">New Alerts</div>
    </div>
    <div class="stat-card text-center">
        <div class="stat-value text-lg text-orange-600">{{ $stats['escalatedAlerts'] }}</div>
        <div class="stat-label text-xs">Escalated</div>
    </div>
    <div class="stat-card text-center">
        <div class="stat-value text-lg text-green-600">{{ $stats['closedAlerts'] }}</div>
        <div class="stat-label text-xs">Closed</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Cluster Map --}}
    <div class="lg:col-span-2 card">
        <div class="card-header flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-sm font-semibold text-gray-900"><i class="fa-solid fa-map-location-dot text-navy mr-1.5"></i> Location Overview — Malaysia</h2>
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                    <input type="checkbox" class="map-filter" data-type="camera" checked>
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-[#1B3A5C]"></span>
                    <span class="text-gray-600">Cameras</span>
                </label>
                <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                    <input type="checkbox" class="map-filter" data-type="detection" checked>
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-[#ef4444]"></span>
                    <span class="text-gray-600">Detections</span>
                </label>
                <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                    <input type="checkbox" class="map-filter" data-type="last_seen" checked>
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-[#f59e0b]"></span>
                    <span class="text-gray-600">Last Seen</span>
                </label>
            </div>
        </div>
        <div class="card-body p-0">
            <div id="cluster-map" style="height: 460px;"></div>
        </div>
        <div class="card-footer text-xs text-gray-500 flex flex-wrap items-center justify-between gap-2">
            <span><i class="fa-solid fa-circle-info mr-1"></i> Zoom in to see individual points. Clusters show grouped counts.</span>
            <span class="text-gray-400">Camera: <span class="font-semibold text-gray-600">{{ $cameraLocations->count() }}</span> &middot; Detection Sites: <span class="font-semibold text-gray-600">{{ $detectionLocations->count() }}</span> &middot; Last Seen: <span class="font-semibold text-gray-600">{{ $lastSeenLocations->count() }}</span></span>
        </div>
    </div>

    {{-- Alert Status Distribution --}}
    <div class="card">
        <div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Alert Status Distribution</h2></div>
        <div class="card-body">
            @php
                $totalAlerts = array_sum($alertsByStatus->toArray()) ?: 1;
            @endphp
            @foreach(['new' => ['label' => 'New', 'color' => 'bg-blue-500'], 'under_review' => ['label' => 'Under Review', 'color' => 'bg-yellow-500'], 'escalated' => ['label' => 'Escalated', 'color' => 'bg-red-500'], 'closed' => ['label' => 'Closed', 'color' => 'bg-green-500'], 'false_positive' => ['label' => 'False Positive', 'color' => 'bg-gray-400']] as $key => $info)
            @php $count = $alertsByStatus[$key] ?? 0; $pct = round(($count / $totalAlerts) * 100); @endphp
            <div class="mb-3 last:mb-0">
                <div class="flex justify-between text-sm mb-1"><span class="text-gray-600">{{ $info['label'] }}</span><span class="font-semibold">{{ $count }} ({{ $pct }}%)</span></div>
                <div class="w-full bg-gray-200 rounded-full h-2.5"><div class="h-2.5 rounded-full {{ $info['color'] }}" style="width: {{ max($pct, 2) }}%"></div></div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Detection Trend Chart --}}
    <div class="card">
        <div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Detection Trend (Last 30 Days)</h2></div>
        <div class="card-body">
            @if($detectionTrend->isNotEmpty())
            <div class="flex items-end gap-1 h-48">
                @php $maxCount = $detectionTrend->max('count') ?: 1; @endphp
                @foreach($detectionTrend as $point)
                <div class="flex-1 flex flex-col items-center gap-1 group relative">
                    <span class="text-[10px] text-gray-500 font-mono">{{ $point->count }}</span>
                    <div class="w-full bg-navy rounded-t hover:bg-navy-light transition-colors cursor-pointer" style="height: {{ max(($point->count / $maxCount) * 100, 2) }}%" title="{{ $point->date }}: {{ $point->count }} detections"></div>
                    <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($point->date)->format('d/m') }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 text-center py-12">No detection data available.</p>
            @endif
        </div>
    </div>

    {{-- Top Cameras --}}
    <div class="card">
        <div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Top Cameras by Detections</h2></div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>#</th><th>Camera</th><th>Location</th><th>Detections</th></tr></thead>
                <tbody>
                    @forelse($topCameras as $i => $camera)
                    <tr>
                        <td class="text-xs text-gray-400">{{ $i + 1 }}</td>
                        <td class="text-sm font-medium">{{ $camera->name }}</td>
                        <td class="text-sm text-gray-500">{{ $camera->location ?? 'N/A' }}</td>
                        <td><span class="badge badge-blue">{{ $camera->detections_count }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-gray-400 py-6">No cameras with detections.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Recent High-Confidence Matches --}}
<div class="card">
    <div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Recent High-Confidence Detections (≥80%)</h2></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Detection ID</th><th>Subject</th><th>Camera</th><th>Location</th><th>Score</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($recentHighMatches as $detection)
                <tr>
                    <td><a href="{{ route('detections.show', $detection) }}" class="text-navy hover:underline font-mono text-xs">{{ $detection->detection_id }}</a></td>
                    <td class="text-sm font-medium">{{ $detection->referenceName() }}</td>
                    <td class="text-sm">{{ $detection->camera->name ?? 'N/A' }}</td>
                    <td class="text-sm text-gray-500">{{ $detection->camera->location ?? 'N/A' }}</td>
                    <td><span class="text-sm text-red-600 font-semibold">{{ number_format($detection->similarity_score, 1) }}%</span></td>
                    <td class="text-xs text-gray-500">{{ $detection->detected_at?->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-gray-400 py-6">No high-confidence detections yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<style>
    .map-filter-wrapper { user-select: none; }
    .map-filter-wrapper input[type="checkbox"] { accent-color: #1B3A5C; }
    .custom-cluster { border-radius: 50%; color: #fff; text-align: center; font-weight: 700; font-family: 'Inter', sans-serif; }
    .custom-cluster-small { width: 30px; height: 30px; line-height: 30px; font-size: 11px; background: rgba(27,58,92,0.75); }
    .custom-cluster-medium { width: 40px; height: 40px; line-height: 40px; font-size: 12px; background: rgba(27,58,92,0.80); }
    .custom-cluster-large { width: 50px; height: 50px; line-height: 50px; font-size: 14px; background: rgba(27,58,92,0.85); }
    .marker-camera { background: #1B3A5C; border: 2px solid #fff; border-radius: 50%; box-shadow: 0 1px 4px rgba(0,0,0,0.3); }
    .marker-detection { background: #ef4444; border: 2px solid #fff; border-radius: 50%; box-shadow: 0 1px 4px rgba(0,0,0,0.3); }
    .marker-last-seen { background: #f59e0b; border: 2px solid #fff; border-radius: 50%; box-shadow: 0 1px 4px rgba(0,0,0,0.3); }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Marker Cluster Map ---
    var map = L.map('cluster-map').setView([4.2105, 108.9758], 5);

    L.tileLayer(@json(config('maps.tile_url')), {
        attribution: @json(config('maps.attribution')),
        maxZoom: @json(config('maps.max_zoom')),
    }).addTo(map);

    // Custom cluster icon styles
    var clusterClasses = {
        small: 'custom-cluster custom-cluster-small',
        medium: 'custom-cluster custom-cluster-medium',
        large: 'custom-cluster custom-cluster-large'
    };

    // Create icon for each marker type
    function createIcon(className) {
        return L.divIcon({
            className: className,
            iconSize: [14, 14],
            iconAnchor: [7, 7],
            popupAnchor: [0, -8]
        });
    }

    var cameraIcon = createIcon('marker-camera');
    var detectionIcon = createIcon('marker-detection');
    var lastSeenIcon = createIcon('marker-last-seen');

    // Create marker cluster group with custom cluster icons
    var markers = L.markerClusterGroup({
        chunkedLoading: true,
        maxClusterRadius: 50,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        iconCreateFunction: function(cluster) {
            var count = cluster.getChildCount();
            var sizeClass = count < 10 ? 'small' : (count < 100 ? 'medium' : 'large');
            return L.divIcon({
                html: '<div class="' + clusterClasses[sizeClass] + '">' + count + '</div>',
                className: '',
                iconSize: null
            });
        }
    });

    // --- Build markers from server data ---
    var allMarkers = { camera: [], detection: [], last_seen: [] };

    // Camera locations
    var cameraData = @json($cameraLocations);
    cameraData.forEach(function(item) {
        if (!item.latitude || !item.longitude) return;
        var marker = L.marker([item.latitude, item.longitude], { icon: cameraIcon });
        marker.bindTooltip('<strong>' + item.name + '</strong><br><span class="text-xs text-gray-500">Camera &middot; ' + (item.location || 'N/A') + '</span>', {
            direction: 'top',
            offset: [0, -10]
        });
        marker.bindPopup(
            '<div style="font-family:Inter,sans-serif;font-size:13px;">' +
            '<strong style="color:#1B3A5C;">' + item.name + '</strong>' +
            '<div style="margin-top:4px;font-size:12px;color:#666;">Type: <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#1B3A5C;margin-right:4px;"></span>Camera</div>' +
            '<div style="font-size:12px;color:#666;">Location: ' + (item.location || 'N/A') + '</div>' +
            '<div style="font-size:12px;color:#666;">Status: <span style="color:' + (item.status === 'online' ? '#16a34a' : '#dc2626') + ';">' + item.status + '</span></div>' +
            '<div style="font-size:11px;color:#999;">' + item.latitude.toFixed(4) + ', ' + item.longitude.toFixed(4) + '</div>' +
            '</div>'
        );
        marker._markerType = 'camera';
        allMarkers.camera.push(marker);
        markers.addLayer(marker);
    });

    // Detection locations
    var detectionData = @json($detectionLocations);
    detectionData.forEach(function(item) {
        if (!item.latitude || !item.longitude) return;
        var marker = L.marker([item.latitude, item.longitude], { icon: detectionIcon });
        marker.bindTooltip('<strong>' + item.name + '</strong><br><span class="text-xs text-gray-500">' + item.detection_count + ' detection(s) &middot; ' + item.avg_score + '% avg</span>', {
            direction: 'top',
            offset: [0, -10]
        });
        marker.bindPopup(
            '<div style="font-family:Inter,sans-serif;font-size:13px;">' +
            '<strong style="color:#dc2626;">' + item.name + '</strong>' +
            '<div style="margin-top:4px;font-size:12px;color:#666;">Type: <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#ef4444;margin-right:4px;"></span>Detection Site</div>' +
            '<div style="font-size:12px;color:#666;">Location: ' + (item.location || 'N/A') + '</div>' +
            '<div style="font-size:12px;color:#666;">Detections: <strong>' + item.detection_count + '</strong></div>' +
            '<div style="font-size:12px;color:#666;">Avg Score: ' + item.avg_score + '%</div>' +
            '<div style="font-size:12px;color:#666;">Last: ' + (item.last_detected || 'N/A') + '</div>' +
            '<div style="font-size:11px;color:#999;">' + item.latitude.toFixed(4) + ', ' + item.longitude.toFixed(4) + '</div>' +
            '</div>'
        );
        marker._markerType = 'detection';
        allMarkers.detection.push(marker);
        markers.addLayer(marker);
    });

    // Last seen locations
    var lastSeenData = @json($lastSeenLocations);
    lastSeenData.forEach(function(item) {
        if (!item.latitude || !item.longitude) return;
        var marker = L.marker([item.latitude, item.longitude], { icon: lastSeenIcon });
        marker.bindTooltip('<strong>' + item.name + '</strong><br><span class="text-xs text-gray-500">Last seen &middot; ' + (item.location || 'N/A') + '</span>', {
            direction: 'top',
            offset: [0, -10]
        });
        marker.bindPopup(
            '<div style="font-family:Inter,sans-serif;font-size:13px;">' +
            '<strong style="color:#d97706;">' + item.name + '</strong>' +
            '<div style="margin-top:4px;font-size:12px;color:#666;">Type: <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#f59e0b;margin-right:4px;"></span>Last Seen Location</div>' +
            '<div style="font-size:12px;color:#666;">Case: ' + (item.case_number || 'N/A') + '</div>' +
            '<div style="font-size:12px;color:#666;">Location: ' + (item.location || 'N/A') + '</div>' +
            '<div style="font-size:12px;color:#666;">Status: ' + item.status + '</div>' +
            '<div style="font-size:11px;color:#999;">' + item.latitude.toFixed(4) + ', ' + item.longitude.toFixed(4) + '</div>' +
            '</div>'
        );
        marker._markerType = 'last_seen';
        allMarkers.last_seen.push(marker);
        markers.addLayer(marker);
    });

    // Add cluster group to map
    map.addLayer(markers);

    // Fit map to show all markers
    var allPoints = [].concat(cameraData, detectionData, lastSeenData)
        .filter(function(d) { return d.latitude && d.longitude; })
        .map(function(d) { return [d.latitude, d.longitude]; });

    if (allPoints.length > 0) {
        var bounds = new L.LatLngBounds(allPoints);
        if (bounds.isValid()) {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 14 });
        }
    }

    // --- Filter Controls ---
    document.querySelectorAll('.map-filter').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var type = this.dataset.type;
            var checked = this.checked;

            if (checked) {
                // Add markers back to cluster group
                allMarkers[type].forEach(function(m) {
                    markers.addLayer(m);
                });
            } else {
                // Remove markers from cluster group
                allMarkers[type].forEach(function(m) {
                    markers.removeLayer(m);
                });
            }
        });
    });
});
</script>
@endpush
