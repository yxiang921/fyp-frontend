<section class="card mb-6"><div class="card-body space-y-4">
<h2 class="text-lg font-semibold">Response and recognition performance — last {{ $analytics['days'] }} days</h2>
@if(auth()->user()->isSuperAdmin())<a class="text-navy underline" href="{{ route('privacy.access-logs') }}">Privacy access logs</a>@endif
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
<p>Detection → first review<br><strong>{{ $analytics['responseMinutes'] ?? 'N/A' }} minutes</strong><br><small>{{ $analytics['responseSamples'] }} timestamped reviews</small></p>
<p>Sighting → first review<br><strong>{{ $analytics['sightingMinutes'] ?? 'N/A' }} minutes</strong></p>
<p>Detection → confirmation<br><strong>{{ $analytics['confirmationRate'] ?? 'N/A' }}%</strong><br><small>{{ $analytics['confirmed'] }} / {{ $analytics['total'] }} detections</small></p>
<p>False-positive rate<br><strong>{{ $analytics['falseRate'] ?? 'N/A' }}%</strong><br><small>Rejected + false positive / all final decisions</small></p>
</div>
<p class="text-xs text-gray-500">Historical reviews without timestamps are excluded from response times. Confirmation rates include pending detections in the denominator. Camera results measure reviewed outcomes, not accuracy against all people passing a camera.</p>
<h3 class="font-semibold">Camera effectiveness</h3>
<table class="data-table"><thead><tr><th>Camera</th><th>Detections</th><th>Confirmed</th><th>False / rejected</th><th>Confirmation rate</th></tr></thead><tbody>
@foreach($analytics['cameras'] as $camera)<tr><td>{{ $camera['name'] }}</td><td>{{ $camera['total'] }}</td><td>{{ $camera['confirmed'] }}</td><td>{{ $camera['false'] }}</td><td>{{ $camera['rate'] ?? 'N/A' }}%</td></tr>@endforeach
</tbody></table>
<h3 class="font-semibold">False-positive trend by detection date</h3>
<table class="data-table"><thead><tr><th>Date</th><th>Detections</th><th>False / rejected</th><th>Share of detections</th></tr></thead><tbody>
@forelse($analytics['trend'] as $day)<tr><td>{{ $day['date'] }}</td><td>{{ $day['total'] }}</td><td>{{ $day['false'] }}</td><td>{{ round(100*$day['false']/$day['total'],1) }}%</td></tr>@empty<tr><td colspan="4">No detections in this period.</td></tr>@endforelse
</tbody></table>
<h3 class="font-semibold">Sighting and detection hotspots</h3><p>Grouped into approximately 1 km cells. Larger circles indicate more reports, not confirmed sightings.</p>
<div id="reporting-hotspots" style="height:320px"></div>
</div></section>
@push('scripts')
<script>
window.addEventListener('load',()=>{
 if (typeof L === 'undefined') return;
 const map=L.map('reporting-hotspots').setView([3.139,101.687],6);
 L.tileLayer(@json(config('maps.tile_url')),{attribution:@json(config('maps.attribution')),maxZoom:@json(config('maps.max_zoom'))}).addTo(map);
 const points={{ Illuminate\Support\Js::from($analytics['hotspots']) }};
 points.forEach(p=>L.circleMarker([p.latitude,p.longitude],{radius:Math.min(40,5+Math.sqrt(p.count)*4),color:'#dc2626',fillOpacity:0.45}).addTo(map).bindPopup(p.sightings+' sightings, '+p.detections+' detections'));
 if(points.length)map.fitBounds(points.map(p=>[p.latitude,p.longitude]),{maxZoom:13,padding:[20,20]});
});
</script>
@endpush
