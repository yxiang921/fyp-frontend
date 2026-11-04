@extends('layouts.app')
@section('title', 'Edit Missing Person')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <a href="{{ route('missing-persons.index') }}">Missing Persons</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <a href="{{ route('missing-persons.show', $missingPerson) }}">{{ $missingPerson->case_number }}</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>Edit</span>
</nav>
@endsection

@section('content')
<div class="max-w-3xl">
    <h1 class="text-xl font-semibold text-gray-900 mb-6">Edit: {{ $missingPerson->case_number }}</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('missing-persons.update', $missingPerson) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="border-b border-gray-200 pb-4 mb-5">
                    <h2 class="text-sm font-semibold text-gray-700">Personal Information</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="case_number" class="form-label">Case Number</label>
                        <input type="text" id="case_number" class="form-input bg-gray-50" value="{{ $missingPerson->case_number }}" readonly>
                        <p class="text-xs text-gray-400 mt-1">Case number cannot be changed</p>
                    </div>
                    <div class="form-group">
                        <label for="full_name" class="form-label">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" id="full_name" name="full_name" class="form-input @error('full_name') border-red-500 @enderror" value="{{ old('full_name', $missingPerson->full_name) }}" required>
                        @error('full_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label for="gender" class="form-label">Gender <span class="text-red-500">*</span></label>
                        <select id="gender" name="gender" class="form-select @error('gender') border-red-500 @enderror" required>
                            <option value="male" {{ old('gender', $missingPerson->gender) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $missingPerson->gender) == 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-input @error('date_of_birth') border-red-500 @enderror" value="{{ old('date_of_birth', $missingPerson->date_of_birth?->format('Y-m-d')) }}">
                        @error('date_of_birth')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label for="national_id" class="form-label">National ID</label>
                        <input type="text" id="national_id" name="national_id" class="form-input @error('national_id') border-red-500 @enderror" value="{{ old('national_id', $missingPerson->national_id) }}">
                        @error('national_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label for="last_seen_date" class="form-label">Last Seen Date <span class="text-red-500">*</span></label>
                        <input type="date" id="last_seen_date" name="last_seen_date" class="form-input @error('last_seen_date') border-red-500 @enderror" value="{{ old('last_seen_date', $missingPerson->last_seen_date?->format('Y-m-d')) }}" required>
                        @error('last_seen_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label for="status" class="form-label">Status <span class="text-red-500">*</span></label>
                        <select id="status" name="status" class="form-select @error('status') border-red-500 @enderror" required>
                            @foreach(\App\Enums\MissingPersonStatus::cases() as $s)
                            <option value="{{ $s->value }}" {{ old('status', $missingPerson->status->value) == $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                            @endforeach
                        </select>
                        @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                @include('missing-persons._identifying-fields', ['missingPerson' => $missingPerson])

                <div class="form-group">
                    <div class="flex items-center justify-between gap-3">
                        <label for="last_seen_location" class="form-label mb-0">Last Seen Location <span class="text-red-500">*</span></label>
                        <button type="button" class="btn btn-secondary btn-sm shrink-0" data-location-search-button onclick="findCoordinates(this)">
                            <i class="fa-solid fa-location-crosshairs"></i> Search coordinates
                        </button>
                    </div>
                    <input type="text" id="last_seen_location" name="last_seen_location" class="form-input @error('last_seen_location') border-red-500 @enderror" value="{{ old('last_seen_location', $missingPerson->last_seen_location) }}" required>
                    <p data-location-search-status class="text-xs text-gray-400 mt-1" role="status">Search again if you want to update the saved coordinates.</p>
                    @error('last_seen_location')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="last_seen_latitude" class="form-label">Last Seen Latitude</label>
                        <input type="text" id="last_seen_latitude" name="last_seen_latitude" class="form-input @error('last_seen_latitude') border-red-500 @enderror" placeholder="e.g. 3.1390" value="{{ old('last_seen_latitude', $missingPerson->last_seen_latitude) }}">
                        <p class="text-xs text-gray-400 mt-1">Decimal degrees (e.g. 3.1390 for KL)</p>
                        @error('last_seen_latitude')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label for="last_seen_longitude" class="form-label">Last Seen Longitude</label>
                        <input type="text" id="last_seen_longitude" name="last_seen_longitude" class="form-input @error('last_seen_longitude') border-red-500 @enderror" placeholder="e.g. 101.6869" value="{{ old('last_seen_longitude', $missingPerson->last_seen_longitude) }}">
                        <p class="text-xs text-gray-400 mt-1">Decimal degrees (e.g. 101.6869 for KL)</p>
                        @error('last_seen_longitude')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="4" class="form-textarea @error('description') border-red-500 @enderror">{{ old('description', $missingPerson->description) }}</textarea>
                    @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="border-b border-gray-200 pb-4 mb-5 mt-6">
                    <h2 class="text-sm font-semibold text-gray-700">Update Photo</h2>
                </div>

                <div class="form-group">
                    @if($missingPerson->primaryPhoto)
                    <div class="mb-3">
                        <p class="text-xs text-gray-500 mb-1">Current photo:</p>
                        <img src="{{ $missingPerson->primaryPhoto->url() }}" alt="Current photo" id="current-photo" class="w-24 h-24 object-cover rounded border">
                    </div>
                    @endif
                    <img id="photo-preview" class="w-32 h-32 object-cover rounded border border-gray-200 mb-3 hidden">
                    <input type="file" id="photo" name="photo" class="form-input @error('photo') border-red-500 @enderror" accept="image/jpeg,image/png,image/jpg" onchange="previewImage(this, 'photo-preview')">
                    <p class="text-xs text-gray-400 mt-1">Leave empty to keep current photo. Supported: JPG, PNG. Max 5MB.</p>
                    @error('photo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3 mt-6 pt-5 border-t border-gray-200">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('missing-persons.show', $missingPerson) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
