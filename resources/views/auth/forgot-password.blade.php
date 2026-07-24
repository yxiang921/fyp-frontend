@extends('layouts.guest')
@section('title', 'Forgot Password')

@section('auth-content')
<h2 class="text-lg font-semibold text-gray-900 mb-1">Reset Password</h2>
<p class="text-sm text-gray-500 mb-6">Enter your email address to receive a password reset link.</p>

@if(session('status'))
<div class="bg-green-50 border border-green-200 rounded p-3 mb-4">
    <p class="text-sm text-green-700">
        <i class="fa-solid fa-circle-check mr-1"></i>
        {{ session('status') }}
    </p>
</div>
@endif

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded p-3 mb-4">
    <p class="text-sm text-red-700">
        <i class="fa-solid fa-circle-exclamation mr-1"></i>
        {{ $errors->first() }}
    </p>
</div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email') }}"
            class="form-input"
            placeholder="you@example.gov.my"
            required
            autofocus
            autocomplete="email"
        >
    </div>

    <button type="submit" class="btn btn-primary w-full">
        <i class="fa-solid fa-paper-plane"></i>
        Send Reset Link
    </button>
</form>

<div class="mt-5 text-center">
    <a href="{{ route('login') }}" class="text-sm text-navy hover:underline">
        <i class="fa-solid fa-arrow-left mr-1"></i> Back to Sign In
    </a>
</div>
@endsection
