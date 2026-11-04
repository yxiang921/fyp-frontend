@extends('layouts.public')
@section('title', __('Report a missing person'))
@section('content')
<div class="card max-w-3xl mx-auto"><div class="card-body space-y-4">
<h1 class="text-xl font-semibold">{{ __('Report a missing person') }}</h1>
@if($errors->any())<div role="alert" class="text-red-600">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<form method="POST" action="{{ route('public.report-missing.store') }}" enctype="multipart/form-data" class="space-y-4">
@csrf
<input name="website" class="hidden" tabindex="-1" autocomplete="off">
@foreach(['full_name' => 'Full name', 'national_id' => 'IC number', 'last_seen_location' => 'Last seen location'] as $key => $label)
<label class="block">{{ __($label) }}<input class="form-input" name="{{ $key }}" value="{{ old($key) }}" maxlength="255" @if($key !== 'national_id') required @endif></label>
@endforeach
<label class="block">{{ __('Gender') }}<select class="form-select" name="gender" required><option value="">—</option>@foreach(['male'=>'Male','female'=>'Female'] as $key=>$label)<option value="{{ $key }}" @selected(old('gender')===$key)>{{ __($label) }}</option>@endforeach</select></label>
<label class="block">{{ __('Date of birth') }}<input class="form-input" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"></label>
<label class="block">{{ __('Last seen date') }}<input class="form-input" type="date" name="last_seen_date" value="{{ old('last_seen_date') }}" required></label>
@include('public._location', ['latName'=>'last_seen_latitude', 'lngName'=>'last_seen_longitude'])
<label class="block">{{ __('Description') }}<textarea class="form-input" name="description" maxlength="5000">{{ old('description') }}</textarea></label>
<label class="block">{{ __('Photo') }}<input type="file" name="photo" accept="image/jpeg,image/png"></label>
@include('public._consent')
@if($errors->has('duplicate_acknowledged'))
<label class="block"><input type="checkbox" name="duplicate_acknowledged" value="1"> {{ __('This is a separate incident. Submit for officer review.') }}</label>
@endif
<button class="btn btn-primary">{{ __('Submit report') }}</button>
</form></div></div>
@endsection
