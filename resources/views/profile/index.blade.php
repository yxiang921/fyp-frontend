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
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-4 mb-6">
                    <div class="relative group">
                        <img src="{{ auth()->user()->avatarUrl() }}" alt="" id="avatar-preview" class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                        <label for="avatar" class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-full opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity">
                            <i class="fa-solid fa-camera text-white text-lg"></i>
                        </label>
                        <input type="file" id="avatar" name="avatar" class="hidden" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewImage(this, 'avatar-preview')">
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Click the avatar to upload a new photo.<br>Supported: JPG, PNG, WebP. Max 5MB.</p>
                        @error('avatar')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="name" class="form-label">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" class="form-input @error('name') border-red-500 @enderror" value="{{ old('name', auth()->user()->name) }}" required>
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input bg-gray-50" value="{{ auth()->user()->email }}" readonly>
                        <p class="text-xs text-gray-400 mt-1">Email cannot be changed</p>
                    </div>
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-input @error('phone') border-red-500 @enderror" value="{{ old('phone', auth()->user()->phone) }}">
                        @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label for="agency" class="form-label">Agency</label>
                        <input type="text" id="agency" name="agency" class="form-input @error('agency') border-red-500 @enderror" value="{{ old('agency', auth()->user()->agency) }}">
                        @error('agency')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-input bg-gray-50" value="{{ auth()->user()->role->label() }}" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Member Since</label>
                        <input type="text" class="form-input bg-gray-50" value="{{ auth()->user()->created_at->format('d M Y') }}" readonly>
                    </div>
                </div>

                <div class="mt-5 pt-5 border-t border-gray-200">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
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
