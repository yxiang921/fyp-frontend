@extends('layouts.app')
@section('title', 'Sighting Report #'.$sighting->id)

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator">›</span><a href="{{ route('sightings.index') }}">Public Sightings</a><span class="separator">›</span><span>#{{ $sighting->id }}</span></nav>
@endsection

@section('content')
@if(session('success'))<div class="alert alert-success mb-4"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>@endif
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
 <div><h1 class="text-xl font-semibold text-gray-900">Public Sighting #{{ $sighting->id }}</h1><p class="text-sm text-gray-500 mt-1">Submitted {{ $sighting->created_at->format('d M Y, H:i') }}</p></div>
 <span class="badge {{ $sighting->status === \App\Enums\SightingStatus::Confirmed ? 'badge-green' : 'badge-blue' }}">{{ $sighting->status->label() }}</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
 <div class="lg:col-span-2 space-y-6">
  <div class="card"><div class="card-header"><h2 class="text-sm font-semibold">Report details</h2></div><div class="card-body"><div class="detail-grid">
   <div class="detail-item"><span class="detail-label">Missing person</span><span class="detail-value"><a class="text-navy hover:underline" href="{{ route('missing-persons.show', $sighting->missingPerson) }}">{{ $sighting->missingPerson->full_name }}</a></span></div>
   <div class="detail-item"><span class="detail-label">Case number</span><span class="detail-value font-mono">{{ $sighting->missingPerson->case_number }}</span></div>
   <div class="detail-item"><span class="detail-label">Reporter</span><span class="detail-value">{{ $sighting->reporter_name ?: 'Anonymous' }}</span></div>
   <div class="detail-item"><span class="detail-label">Phone</span><span class="detail-value">{{ $sighting->reporter_phone ?: 'Not provided' }}</span></div>
   <div class="detail-item"><span class="detail-label">Location</span><span class="detail-value">{{ $sighting->location }}</span></div>
   <div class="detail-item"><span class="detail-label">Sighted at</span><span class="detail-value">{{ $sighting->sighted_at?->format('d M Y, H:i') }}</span></div>
  </div><div class="mt-4 pt-4 border-t"><p class="detail-label mb-1">Witness notes</p><p class="text-sm text-gray-700 whitespace-pre-line">{{ $sighting->notes ?: 'No notes provided.' }}</p></div></div></div>

  <div class="card"><div class="card-header"><h2 class="text-sm font-semibold">Evidence</h2></div><div class="card-body grid grid-cols-1 sm:grid-cols-2 gap-4">
   @forelse($sighting->evidence as $evidence)
    <div class="border rounded p-3">
     @if(!$evidence->purged_at && $evidence->expires_at->isFuture())
      @if(str_starts_with($evidence->mime_type, 'image/'))
       <img src="{{ route('sightings.evidence', $evidence) }}" alt="Sighting evidence" class="w-full h-56 object-contain bg-gray-100 rounded">
      @elseif(str_starts_with($evidence->mime_type, 'video/'))
       <video controls class="w-full h-56 bg-black rounded"><source src="{{ route('sightings.evidence', $evidence) }}" type="{{ $evidence->mime_type }}"></video>
      @endif
      <a class="text-navy underline text-sm mt-2 inline-block" href="{{ route('sightings.evidence', $evidence) }}">Open original</a>
     @else <p class="text-gray-500">Evidence expired</p> @endif
     <p class="text-[10px] text-gray-400 break-all mt-2">SHA-256: {{ $evidence->sha256 }}</p>
    </div>
   @empty <p class="text-sm text-gray-400">No evidence attached.</p> @endforelse
  </div></div>

  @if($sighting->statusHistory->isNotEmpty())
  <div class="card"><div class="card-header"><h2 class="text-sm font-semibold">Status history</h2></div><div class="card-body space-y-4">
   @foreach($sighting->statusHistory as $history)<div class="border-l-2 border-navy pl-3"><p class="text-sm font-medium">{{ ucfirst(str_replace('_',' ', $history->from_status)) }} → {{ ucfirst(str_replace('_',' ', $history->to_status)) }}</p><p class="text-xs text-gray-500">{{ $history->performedBy?->name ?? 'System' }} · {{ $history->created_at->format('d M Y, H:i') }}</p>@if($history->notes)<p class="text-sm mt-1">{{ $history->notes }}</p>@endif</div>@endforeach
  </div></div>
  @endif
 </div>

 <div class="space-y-6">
  @if($sighting->alert)<div class="card"><div class="card-header"><h2 class="text-sm font-semibold">Generated alert</h2></div><div class="card-body"><a href="{{ route('alerts.show', $sighting->alert) }}" class="btn btn-warning w-full"><i class="fa-solid fa-bell"></i> {{ $sighting->alert->alert_id }}</a></div></div>@endif
  <div class="card"><div class="card-header"><h2 class="text-sm font-semibold">Update status</h2></div><div class="card-body">
   <form method="POST" action="{{ route('sightings.update-status', $sighting) }}">@csrf @method('PATCH')
    <div class="form-group"><label class="form-label" for="status">Status</label><select id="status" name="status" class="form-select">@foreach($statuses as $status)<option value="{{ $status->value }}" {{ $sighting->status === $status ? 'selected' : '' }}>{{ $status->label() }}</option>@endforeach</select></div>
    <div class="form-group"><label class="form-label" for="notes">Review notes</label><textarea id="notes" name="notes" rows="4" class="form-textarea" placeholder="Record the reason and verification steps..."></textarea></div>
    <button class="btn btn-primary w-full" type="submit"><i class="fa-solid fa-arrow-right-arrow-left"></i> Update status</button>
   </form>
   <p class="text-xs text-gray-500 mt-3">Confirming creates one alert and notifies staff plus patrol units responsible for this location.</p>
  </div></div>
  @if($sighting->latitude !== null && $sighting->longitude !== null)<div class="card"><div class="card-header"><h2 class="text-sm font-semibold">Reported location</h2></div><div id="sighting-map" style="height:280px"></div></div>@endif
 </div>
</div>
@endsection

@if($sighting->latitude !== null && $sighting->longitude !== null)
@push('scripts')<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script><script>
document.addEventListener('DOMContentLoaded',()=>{const p=[{{ (float)$sighting->latitude }},{{ (float)$sighting->longitude }}],m=L.map('sighting-map').setView(p,15);L.tileLayer(@json(config('maps.tile_url')),{attribution:@json(config('maps.attribution')),maxZoom:@json(config('maps.max_zoom'))}).addTo(m);L.marker(p).addTo(m).bindPopup(@json($sighting->location)).openPopup();});
</script>@endpush
@endif
