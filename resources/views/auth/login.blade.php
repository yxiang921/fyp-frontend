@extends('layouts.guest')
@section('title', 'Sign In')

@section('auth-content')
<h2 class="text-lg font-semibold text-gray-900 mb-1">Sign In</h2>
<p class="text-sm text-gray-500 mb-6">Enter your credentials to access the system.</p>

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded p-3 mb-4">
    <p class="text-sm text-red-700">
        <i class="fa-solid fa-circle-exclamation mr-1"></i>
        {{ $errors->first() }}
    </p>
</div>
@endif

<form method="POST" action="{{ route('login') }}">
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

    <div class="form-group">
        <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="form-label mb-0">Password</label>
            <a href="{{ route('password.request') }}" class="text-xs text-navy hover:underline">Forgot password?</a>
        </div>
        <input
            type="password"
            id="password"
            name="password"
            class="form-input"
            placeholder="••••••••"
            required
            autocomplete="current-password"
        >
    </div>

    <div class="flex items-center mb-5">
        <input type="checkbox" id="remember" name="remember" class="rounded-sm border-gray-300 text-navy focus:ring-navy">
        <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
    </div>

    <button type="submit" class="btn btn-primary w-full">
        <i class="fa-solid fa-right-to-bracket"></i>
        Sign In
    </button>
</form>

<div class="mt-6 pt-5 border-t border-gray-200 text-center">
    <p class="text-xs text-gray-500">
        This is a restricted PDRM system.<br>
        Unauthorized access is prohibited.
    </p>
</div>
@endsection
