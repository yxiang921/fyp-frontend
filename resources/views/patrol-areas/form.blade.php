@extends('layouts.app')
@section('title', $area->exists ? 'Edit Patrol Area' : 'Create Patrol Area')
@section('content')
<div class="max-w-2xl"><h1 class="text-xl font-semibold mb-6">{{ $area->exists ? 'Edit Patrol Area' : 'Create Patrol Area' }}</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ $area->exists ? route('patrol-areas.update', $area) : route('patrol-areas.store') }}" class="space-y-4">
@csrf @if($area->exists) @method('PUT') @endif
<label class="block form-label">Area name<input class="form-input" name="name" required maxlength="100" value="{{ old('name', $area->name) }}" placeholder="Skudai"></label>
<label class="block form-label">Code<input class="form-input" name="code" required maxlength="30" value="{{ old('code', $area->code) }}" placeholder="SKUDAI"></label>
<label class="block form-label">Description<textarea class="form-textarea" name="description" maxlength="1000">{{ old('description', $area->description) }}</textarea></label>
<div class="grid grid-cols-2 gap-4"><label class="form-label">Centre latitude<input class="form-input" name="center_latitude" type="number" step="any" value="{{ old('center_latitude', $area->center_latitude) }}"></label>
<label class="form-label">Centre longitude<input class="form-input" name="center_longitude" type="number" step="any" value="{{ old('center_longitude', $area->center_longitude) }}"></label></div>
<label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $area->exists ? $area->is_active : true))> Active</label>
<div class="flex gap-3"><button class="btn btn-primary">Save</button><a class="btn btn-secondary" href="{{ route('patrol-areas.index') }}">Cancel</a></div>
</form></div></div></div>
@endsection
