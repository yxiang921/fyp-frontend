@extends('layouts.public')
@section('title', __('Track a case'))
@section('content')
<div class="card max-w-xl mx-auto"><div class="card-body space-y-4">
    <h1 class="text-xl font-semibold">{{ __('Track a case') }}</h1>
    @error('case_number')<p role="alert" class="text-red-600">{{ $message }}</p>@enderror
    <p>{{ __('Enter your case number. Only case status and dates are shown to protect privacy.') }}</p>
    <form method="POST" action="{{ route('public.track') }}" class="space-y-3">
        @csrf
        <label class="form-label" for="case_number">{{ __('Case number') }}</label>
        <input class="form-input" name="case_number" id="case_number" required maxlength="40" value="{{ old('case_number', $result['case_number'] ?? '') }}">
        <button class="btn btn-primary">{{ __('Check status') }}</button>
    </form>
    @if(isset($result))
        <dl><dt>{{ __('Case number') }}</dt><dd>{{ $result['case_number'] }}</dd>
            <dt>{{ __('Status') }}</dt><dd>{{ __(ucfirst($result['status'])) }}</dd>
            <dt>{{ __('Submitted') }}</dt><dd>{{ $result['submitted_at'] }}</dd>
            <dt>{{ __('Updated') }}</dt><dd>{{ $result['updated_at'] }}</dd></dl>
    @endif
</div></div>
@endsection
