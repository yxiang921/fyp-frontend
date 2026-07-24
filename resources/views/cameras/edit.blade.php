@extends('layouts.app')
@section('title', 'Edit Camera')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <a href="{{ route('cameras.index') }}">Cameras</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <a href="{{ route('cameras.show', $camera) }}">{{ $camera->camera_id }}</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>Edit</span>
</nav>
@endsection

@section('content')
<div class="max-w-2xl">
    <h1 class="text-xl font-semibold text-gray-900 mb-6">Edit Camera: {{ $camera->camera_id }}</h1>
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('cameras.update', $camera) }}">
            @csrf
            @method('PUT')

            {{-- Camera Info --}}
            <div class="border-b border-gray-200 pb-4 mb-5">
                <h2 class="text-sm font-semibold text-gray-700">Camera Information</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Camera ID</label>
                    <input type="text" class="form-input bg-gray-50" value="{{ $camera->camera_id }}" readonly>
                </div>
                <div class="form-group">
                    <label for="name" class="form-label">Camera Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" class="form-input @error('name') border-red-500 @enderror" value="{{ old('name', $camera->name) }}" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="form-group md:col-span-2">
                    <label for="location" class="form-label">Location <span class="text-red-500">*</span></label>
                    <input type="text" id="location" name="location" class="form-input @error('location') border-red-500 @enderror" value="{{ old('location', $camera->location) }}" required>
                    @error('location')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- IP Camera Configuration --}}
            <div class="border-b border-gray-200 pb-4 mb-5 mt-6">
                <h2 class="text-sm font-semibold text-gray-700">IP Camera Configuration</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label for="ip_address" class="form-label">IP Address</label>
                    <input type="text" id="ip_address" name="ip_address" class="form-input @error('ip_address') border-red-500 @enderror" value="{{ old('ip_address', $camera->ip_address) }}" placeholder="e.g. 192.168.1.100">
                    @error('ip_address')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="port" class="form-label">Port</label>
                    <input type="number" id="port" name="port" class="form-input @error('port') border-red-500 @enderror" value="{{ old('port', $camera->port) }}" placeholder="80" min="1" max="65535">
                    @error('port')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="stream_type" class="form-label">Stream Type</label>
                    <select id="stream_type" name="stream_type" class="form-select @error('stream_type') border-red-500 @enderror">
                        <option value="webcam" {{ old('stream_type', $camera->stream_type) == 'webcam' ? 'selected' : '' }}>Webcam (Built-in / USB)</option>
                        <option value="mjpeg" {{ old('stream_type', $camera->stream_type) == 'mjpeg' ? 'selected' : '' }}>MJPEG (Motion JPEG)</option>
                        <option value="snapshot" {{ old('stream_type', $camera->stream_type) == 'snapshot' ? 'selected' : '' }}>Snapshot (Still Image)</option>
                        <option value="custom" {{ old('stream_type', $camera->stream_type) == 'custom' ? 'selected' : '' }}>Custom URL (Direct Link)</option>
                        <option value="rtsp" {{ old('stream_type', $camera->stream_type) == 'rtsp' ? 'selected' : '' }}>RTSP (Requires Media Server)</option>
                        <option value="hls" {{ old('stream_type', $camera->stream_type) == 'hls' ? 'selected' : '' }}>HLS (HTTP Live Streaming)</option>
                    </select>
                    @error('stream_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="stream_url" class="form-label">
                        Stream Path / Full URL (Optional)
                    </label>
                    <input type="text" id="stream_url" name="stream_url" class="form-input @error('stream_url') border-red-500 @enderror" value="{{ old('stream_url', $camera->stream_url) }}" placeholder="e.g. /video/mjpeg.cgi or http://192.168.1.16:8080/video">
                    <p class="text-xs text-gray-400 mt-1">For Custom URL type, enter the full URL. Otherwise enter just the path.</p>
                    @error('stream_url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="username" class="form-label">Username (Optional)</label>
                    <input type="text" id="username" name="username" class="form-input @error('username') border-red-500 @enderror" value="{{ old('username', $camera->username) }}" placeholder="Camera login username" autocomplete="off">
                    @error('username')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Password (Optional)</label>
                    <input type="password" id="password" name="password" class="form-input @error('password') border-red-500 @enderror" placeholder="Leave empty to keep current" autocomplete="off">
                    <p class="text-xs text-gray-400 mt-1">Leave empty to keep current password.</p>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Additional Info --}}
            <div class="border-b border-gray-200 pb-4 mb-5 mt-6">
                <h2 class="text-sm font-semibold text-gray-700">Additional Information</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label for="status" class="form-label">Status <span class="text-red-500">*</span></label>
                    <select id="status" name="status" class="form-select @error('status') border-red-500 @enderror" required>
                        <option value="online" {{ old('status', $camera->status->value) == 'online' ? 'selected' : '' }}>Online</option>
                        <option value="offline" {{ old('status', $camera->status->value) == 'offline' ? 'selected' : '' }}>Offline</option>
                        <option value="maintenance" {{ old('status', $camera->status->value) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                    @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="latitude" class="form-label">Latitude</label>
                    <input type="text" id="latitude" name="latitude" class="form-input @error('latitude') border-red-500 @enderror" value="{{ old('latitude', $camera->latitude) }}">
                    @error('latitude')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="longitude" class="form-label">Longitude</label>
                    <input type="text" id="longitude" name="longitude" class="form-input @error('longitude') border-red-500 @enderror" value="{{ old('longitude', $camera->longitude) }}">
                    @error('longitude')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="3" class="form-textarea @error('description') border-red-500 @enderror">{{ old('description', $camera->description) }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3 mt-6 pt-5 border-t border-gray-200">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Changes</button>
                <a href="{{ route('cameras.show', $camera) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div></div>
</div>
@endsection
