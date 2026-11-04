@extends('layouts.public')
@section('title', __('Report a sighting'))
@section('content')
<div class="card max-w-3xl mx-auto"><div class="card-body space-y-4">
<h1 class="text-xl font-semibold">{{ __('Report a sighting') }} — {{ $missingPerson->case_number }}</h1>
@if(session('success'))<p role="status">{{ session('success') }}</p>@endif
@if($errors->any())<div role="alert" class="text-red-600">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<form method="POST" enctype="multipart/form-data" class="space-y-4">
@csrf
@foreach(['location'=>'Location', 'reporter_name'=>'Your name (optional)', 'reporter_phone'=>'Phone (optional)'] as $key=>$label)
<label class="block">{{ __($label) }}<input class="form-input" name="{{ $key }}" value="{{ old($key) }}" @if($key === 'location') required @endif></label>
@endforeach
<label class="block">{{ __('Date and time') }}<input class="form-input" type="datetime-local" name="sighted_at" required value="{{ old('sighted_at') }}"></label>
@include('public._location', ['latName'=>'latitude', 'lngName'=>'longitude'])
<label class="block">{{ __('Notes (optional)') }}<textarea class="form-input" name="notes" maxlength="5000">{{ old('notes') }}</textarea></label>
<label class="block">{{ __('Photos / videos (up to 3, 20 MB each)') }}<input type="file" name="evidence[]" multiple accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm"></label>
@include('public._consent')
<button class="btn btn-primary">{{ __('Submit report') }}</button>
</form></div></div>
@endsection
