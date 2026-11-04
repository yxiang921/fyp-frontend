@extends('layouts.app')
@section('title', 'Patrol Areas')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-semibold">Patrol Areas</h1><p class="text-sm text-gray-500">Alerts are routed from each camera to patrol units assigned to its area.</p></div>
    <a class="btn btn-primary" href="{{ route('patrol-areas.create') }}">Add Patrol Area</a>
</div>
<div class="card overflow-x-auto"><table class="data-table">
<thead><tr><th>Area</th><th>Code</th><th>Patrol units</th><th>Cameras</th><th>Status</th><th></th></tr></thead>
<tbody>@forelse($areas as $area)<tr>
<td><strong>{{ $area->name }}</strong><p class="text-xs text-gray-500">{{ $area->description }}</p></td><td>{{ $area->code }}</td>
<td>{{ $area->users_count }}</td><td>{{ $area->cameras_count }}</td><td>{{ $area->is_active ? 'Active' : 'Inactive' }}</td>
<td class="flex gap-2"><a class="btn btn-sm btn-secondary" href="{{ route('patrol-areas.edit', $area) }}">Edit</a>
<form method="POST" action="{{ route('patrol-areas.destroy', $area) }}" onsubmit="return confirm('Delete this patrol area?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form></td>
</tr>@empty<tr><td colspan="6">No patrol areas configured.</td></tr>@endforelse</tbody>
</table></div>
@endsection
