@extends('layouts.app')
@section('title', 'My Profile')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>My Profile</span>
</nav>
@endsection

@section('content')
<div class="max-w-3xl">
    <h1 class="text-xl font-semibold text-gray-900 mb-6">My Profile</h1>

    <div class="card mb-6">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-gray-900">Profile Information</h2>
        </div>
        <div class="card-body">
            <div class="flex items-center gap-4 mb-6">
                <img src="{{ auth()->user()->avatarUrl() }}" alt="" class="w-16 h-16 rounded-full">
                <div>
                    <p class="text-lg font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                    <p class="text-sm text-gray-500">{{ auth()->user()->role->label() }}</p>
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">{{ auth()->user()->email }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value">{{ auth()->user()->phone ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Agency</span>
                    <span class="detail-value">{{ auth()->user()->agency ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Member Since</span>
                    <span class="detail-value">{{ auth()->user()->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-gray-900">Account Actions</h2>
        </div>
        <div class="card-body space-y-3">
            <a href="{{ route('profile.security') }}" class="flex items-center gap-3 p-3 rounded border border-gray-200 hover:bg-gray-50 transition-colors">
                <i class="fa-solid fa-lock text-gray-400"></i>
                <div>
                    <p class="text-sm font-medium text-gray-900">Change Password</p>
                    <p class="text-xs text-gray-500">Update your account password</p>
                </div>
                <i class="fa-solid fa-chevron-right text-gray-300 ml-auto"></i>
            </a>
        </div>
    </div>
</div>
@endsection
