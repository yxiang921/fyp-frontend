<div class="card mb-5"><div class="card-body">
<h2 class="font-semibold">Case map — sightings and detections</h2>
<p class="text-sm">Blue: public sightings · Red: camera detections · Purple: last seen. Reports and matches require review.</p>
@if(count($points))<div id="{{ $mapId }}" style="height:340px" class="mt-3"></div>
@else<p>No recorded coordinates yet.</p>@endif
</div></div>
@push('styles')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(() => {
 const points = {{ Illuminate\Support\Js::from($points) }}, el = document.getElementById(@json($mapId));
 if(!el || !points.length) return;
 const map=L.map(el).setView([3.139,101.687],7);
 L.tileLayer(@json(config('maps.tile_url')),{attribution:@json(config('maps.attribution')),maxZoom:@json(config('maps.max_zoom'))}).addTo(map);
 points.forEach(p=>{const label=document.createElement('div');label.textContent=[p.type,p.label,p.status,p.at].filter(Boolean).join(' · ');
 L.circleMarker([p.latitude,p.longitude],{color:p.type==='sighting'?'#2563eb':p.type==='detection'?'#dc2626':'#9333ea',radius:8}).addTo(map).bindPopup(label);});
 map.fitBounds(points.map(p=>[p.latitude,p.longitude]),{padding:[24,24],maxZoom:16});
})();
</script>
@endpush
