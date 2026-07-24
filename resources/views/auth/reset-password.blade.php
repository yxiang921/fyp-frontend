@extends('layouts.guest')
@section('title', 'Reset Password')

@section('auth-content')
<h2 class="text-lg font-semibold text-gray-900 mb-1">Set New Password</h2>
<p class="text-sm text-gray-500 mb-6">Enter your new password below.</p>

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded p-3 mb-4">
    @foreach($errors->all() as $error)
    <p class="text-sm text-red-700">{{ $error }}</p>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" id="email" name="email" value="{{ old('email', $email) }}" class="form-input" required readonly>
    </div>

    <div class="form-group">
        <label for="password" class="form-label">New Password</label>
        <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required autofocus>
    </div>

    <div class="form-group">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn btn-primary w-full">
        <i class="fa-solid fa-lock"></i>
        Reset Password
    </button>
</form>
@endsection
