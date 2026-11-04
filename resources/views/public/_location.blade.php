<div class="space-y-2">
<button type="button" id="locate" class="btn btn-secondary">{{ __('Use current GPS location') }}</button>
<p>{{ __('Drag the pin or tap the map to set the location. GPS is optional.') }}</p>
<p id="location-error" role="status"></p>
<div id="location-map" style="height:280px"></div>
<label>{{ __('Latitude') }} <input class="form-input" id="latitude" name="{{ $latName }}" type="number" step="any" min="-90" max="90" value="{{ old($latName) }}"></label>
<label>{{ __('Longitude') }} <input class="form-input" id="longitude" name="{{ $lngName }}" type="number" step="any" min="-180" max="180" value="{{ old($lngName) }}"></label>
</div>
@push('styles')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(() => {
 const lat = document.getElementById('latitude'), lng = document.getElementById('longitude');
 const map = L.map('location-map').setView([3.139,101.687],7);
 L.tileLayer(@json(config('maps.tile_url')),{attribution:@json(config('maps.attribution')),maxZoom:@json(config('maps.max_zoom'))}).addTo(map);
 let pin;
 function setPoint(a,b) {
   if (!Number.isFinite(a)||!Number.isFinite(b)||Math.abs(a)>90||Math.abs(b)>180) return;
   lat.value=a.toFixed(7); lng.value=b.toFixed(7);
   if (pin) pin.setLatLng([a,b]); else { pin=L.marker([a,b],{draggable:true}).addTo(map); pin.on('dragend',()=>{const p=pin.getLatLng();setPoint(p.lat,p.lng);}); }
 }
 map.on('click',e=>setPoint(e.latlng.lat,e.latlng.lng));
 if(lat.value && lng.value) {setPoint(Number(lat.value),Number(lng.value));map.setView(pin.getLatLng(),14);}
 [lat,lng].forEach(input=>input.addEventListener('change',()=>{if(lat.value&&lng.value)setPoint(Number(lat.value),Number(lng.value));}));
 document.getElementById('locate').onclick=()=> {
   const error=document.getElementById('location-error');
   if (!navigator.geolocation) {error.textContent=@json(__('GPS unavailable. Set the pin manually.'));return;}
   navigator.geolocation.getCurrentPosition(p=>{setPoint(p.coords.latitude,p.coords.longitude);map.setView(pin.getLatLng(),16);error.textContent='';},()=>error.textContent=@json(__('GPS unavailable. Set the pin manually.')),{timeout:15000});
 };
})();
</script>
@endpush
