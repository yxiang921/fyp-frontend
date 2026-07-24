@extends('layouts.app')
@section('title', 'Camera: ' . $camera->name)

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <a href="{{ route('cameras.index') }}">Cameras</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>{{ $camera->camera_id }}</span>
</nav>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success mb-4">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger mb-4">
    <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
</div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Camera: {{ $camera->name }}</h1>
        <p class="text-sm text-gray-500 mt-1">Camera ID: <code class="font-mono">{{ $camera->camera_id }}</code></p>
    </div>
    <div class="flex gap-2">
        @if($camera->hasIpAddress())
        <a href="{{ route('cameras.live', $camera) }}" class="btn btn-success">
            <i class="fa-solid fa-play"></i> Live View
        </a>
        @endif
        <a href="{{ route('cameras.edit', $camera) }}" class="btn btn-secondary"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
        @if(auth()->user()->isSuperAdmin())
        <form method="POST" action="{{ route('cameras.destroy', $camera) }}" onsubmit="return confirm('Delete camera {{ $camera->camera_id }}?')" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i> Delete</button>
        </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Camera Information --}}
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-gray-900">Camera Information</h2>
                <span class="badge {{ $camera->status->badge() ?? 'badge-gray' }}">{{ $camera->status->label() }}</span>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Camera ID</span><span class="detail-value font-mono">{{ $camera->camera_id }}</span></div>
                    <div class="detail-item"><span class="detail-label">Name</span><span class="detail-value font-medium">{{ $camera->name }}</span></div>
                    <div class="detail-item"><span class="detail-label">Location</span><span class="detail-value">{{ $camera->location }}</span></div>
                    <div class="detail-item"><span class="detail-label">Status</span><span class="detail-value"><span class="badge {{ $camera->status->badge() ?? 'badge-gray' }}">{{ $camera->status->label() }}</span></span></div>
                    <div class="detail-item"><span class="detail-label">Coordinates</span><span class="detail-value font-mono text-xs">{{ $camera->latitude }}, {{ $camera->longitude }}</span></div>
                </div>
            </div>
        </div>

        {{-- IP Camera Configuration --}}
        @if($camera->hasIpAddress())
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-gray-900">IP Camera Configuration</h2>
                <a href="{{ route('cameras.live', $camera) }}" class="btn btn-sm btn-success">
                    <i class="fa-solid fa-play"></i> Live View
                </a>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">IP Address</span><span class="detail-value font-mono">{{ $camera->ip_address }}</span></div>
                    <div class="detail-item"><span class="detail-label">Port</span><span class="detail-value">{{ $camera->port }}</span></div>
                    <div class="detail-item"><span class="detail-label">Stream Type</span><span class="detail-value">
                        <span class="badge badge-blue">{{ strtoupper($camera->stream_type) }}</span>
                    </span></div>
                    <div class="detail-item"><span class="detail-label">Stream URL</span><span class="detail-value font-mono text-xs break-all">{{ $camera->getStreamUrl() }}</span></div>
                    @if($camera->username)
                    <div class="detail-item"><span class="detail-label">Username</span><span class="detail-value">{{ $camera->username }}</span></div>
                    @endif
                </div>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-body text-center py-6 text-gray-400">
                <i class="fa-solid fa-network-wired text-3xl mb-2 block"></i>
                <p class="text-sm">No IP camera configured.</p>
                <a href="{{ route('cameras.edit', $camera) }}" class="text-xs text-navy hover:underline mt-1 inline-block">Add IP configuration</a>
            </div>
        </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="card p-4 text-center">
                <i class="fa-solid fa-camera text-gray-400 text-lg mb-2"></i>
                <p class="text-xl font-bold text-gray-900">{{ number_format($camera->detections()->count()) }}</p>
                <p class="text-xs text-gray-500">Total Detections</p>
            </div>
            <div class="card p-4 text-center">
                <i class="fa-solid fa-bell text-gray-400 text-lg mb-2"></i>
                <p class="text-xl font-bold text-gray-900">{{ $camera->alerts()->count() }}</p>
                <p class="text-xs text-gray-500">Alerts Generated</p>
            </div>
            <div class="card p-4 text-center">
                <i class="fa-solid fa-calendar-day text-gray-400 text-lg mb-2"></i>
                <p class="text-xl font-bold text-gray-900">{{ $camera->detections()->whereDate('created_at', today())->count() }}</p>
                <p class="text-xs text-gray-500">Today</p>
            </div>
            <div class="card p-4 text-center">
                <i class="fa-solid fa-calendar-week text-gray-400 text-lg mb-2"></i>
                <p class="text-xl font-bold text-gray-900">{{ $camera->detections()->where('created_at', '>=', now()->subWeek())->count() }}</p>
                <p class="text-xs text-gray-500">This Week</p>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Registered By --}}
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Registration Info</h2></div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Registered By</span><span class="detail-value">{{ $camera->registeredBy->name ?? 'N/A' }}</span></div>
                    <div class="detail-item"><span class="detail-label">Registered On</span><span class="detail-value text-xs">{{ $camera->created_at->format('d M Y, H:i') }}</span></div>
                    <div class="detail-item"><span class="detail-label">Description</span><span class="detail-value text-sm">{{ $camera->description ?? 'No description.' }}</span></div>
                </div>
            </div>
        </div>

        {{-- Recent Detections --}}
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Recent Detections</h2></div>
            <div class="card-body divide-y divide-gray-100">
                @forelse($camera->detections as $detection)
                <div class="py-2 flex items-center justify-between text-sm">
                    <span class="font-mono text-xs text-navy">{{ $detection->detection_id }}</span>
                    <span>{{ $detection->referenceName() }}</span>
                    <span class="font-semibold {{ $detection->similarity_score >= 80 ? 'text-red-600' : 'text-gray-600' }}">
                        {{ number_format($detection->similarity_score, 1) }}%
                    </span>
                </div>
                @empty
                <div class="py-4 text-center text-gray-400 text-sm">No detections yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
