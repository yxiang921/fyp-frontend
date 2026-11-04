@extends('layouts.app')
@section('title', 'Report Missing Person')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>Report Missing Person</span>
</nav>
@endsection

@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-gray-900">Report a Missing Person</h1>
        <p class="text-sm text-gray-500 mt-1">Submit a missing person report. Your case will be reviewed by a PDRM officer before facial recognition monitoring begins.</p>
    </div>

    <div class="disclaimer mb-6">
        <i class="fa-solid fa-info-circle mt-0.5 flex-shrink-0"></i>
        <div>
            <p class="font-medium">Important Information</p>
            <p class="mt-0.5">All submitted reports will be verified by PDRM officers before activation. You will be able to track the status of your case after submission. Please ensure all information provided is accurate.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('public.report-missing.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="border-b border-gray-200 pb-4 mb-5">
                    <h2 class="text-sm font-semibold text-gray-700">Missing Person Information</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group md:col-span-2">
                        <label for="full_name" class="form-label">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" id="full_name" name="full_name" class="form-input @error('full_name') border-red-500 @enderror" value="{{ old('full_name') }}" placeholder="Full name as per IC" required>
                        @error('full_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label for="gender" class="form-label">Gender <span class="text-red-500">*</span></label>
                        <select id="gender" name="gender" class="form-select @error('gender') border-red-500 @enderror" required>
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-input @error('date_of_birth') border-red-500 @enderror" value="{{ old('date_of_birth') }}">
                        @error('date_of_birth')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label for="national_id" class="form-label">National ID (IC Number)</label>
                        <input type="text" id="national_id" name="national_id" class="form-input @error('national_id') border-red-500 @enderror" placeholder="XXXXXX-XX-XXXX" value="{{ old('national_id') }}">
                        @error('national_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label for="last_seen_date" class="form-label">Last Seen Date <span class="text-red-500">*</span></label>
                        <input type="date" id="last_seen_date" name="last_seen_date" class="form-input @error('last_seen_date') border-red-500 @enderror" value="{{ old('last_seen_date') }}" required>
                        @error('last_seen_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group md:col-span-2">
                        <label for="last_seen_location" class="form-label">Last Seen Location <span class="text-red-500">*</span></label>
                        <input type="text" id="last_seen_location" name="last_seen_location" class="form-input @error('last_seen_location') border-red-500 @enderror" placeholder="Full address or location description" value="{{ old('last_seen_location') }}" required>
                        @error('last_seen_location')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group md:col-span-2">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="4" class="form-textarea @error('description') border-red-500 @enderror" placeholder="Physical description, clothing worn when last seen, circumstances of disappearance, etc.">{{ old('description') }}</textarea>
                        @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="border-b border-gray-200 pb-4 mb-5 mt-6">
                    <h2 class="text-sm font-semibold text-gray-700">Photo <span class="text-red-500">*</span></h2>
                </div>

                <div class="form-group">
                    <label for="photo" class="form-label">Upload a recent photo of the missing person <span class="text-red-500">*</span></label>
                    <img id="photo-preview" class="w-32 h-32 object-cover rounded border border-gray-200 mb-3 hidden">
                    <input type="file" id="photo" name="photo" class="form-input @error('photo') border-red-500 @enderror" accept="image/jpeg,image/png,image/jpg" required onchange="previewImage(this, 'photo-preview')">
                    <p class="text-xs text-gray-400 mt-1">A clear, recent photo helps facial recognition. JPG or PNG, max 5MB.</p>
                    @error('photo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3 mt-6 pt-5 border-t border-gray-200">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-paper-plane"></i> Submit Report
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
