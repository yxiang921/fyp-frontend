@extends('layouts.app')
@section('title', 'Security')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <a href="{{ route('profile') }}">Profile</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>Security</span>
</nav>
@endsection

@section('content')
<div class="max-w-lg">
    <h1 class="text-xl font-semibold text-gray-900 mb-6">Change Password</h1>

    @if(session('status'))
    <div class="bg-green-50 border border-green-200 rounded p-3 mb-4">
        <p class="text-sm text-green-700"><i class="fa-solid fa-circle-check mr-1"></i> {{ session('status') }}</p>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="#">
                @csrf
                <div class="form-group">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-lock"></i> Update Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
