@extends('layouts.app')
@section('title', 'Live View: ' . $camera->name)

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <a href="{{ route('cameras.index') }}">Cameras</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <a href="{{ route('cameras.show', $camera) }}">{{ $camera->camera_id }}</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>Live View</span>
</nav>
@endsection

@push('styles')
<style>
    .live-feed-container {
        position: relative;
        background: #000;
        border-radius: 0.5rem;
        overflow: hidden;
        aspect-ratio: 4 / 3;
        max-width: 800px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 480px;
    }
    .live-feed-container img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .live-feed-container .no-signal {
        color: #6b7280;
        text-align: center;
    }
    .live-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
        background: rgba(220, 38, 38, 0.9);
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 4px;
        z-index: 10;
    }
    .live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #fff;
        animation: live-pulse 1.5s ease-in-out infinite;
    }
    @keyframes live-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
</style>
@endpush

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-3 mb-2">
        <h1 class="text-xl font-semibold text-gray-900">{{ $camera->name }}</h1>
        <div class="live-badge" style="position: static; background: {{ ($recognitionStatus['running'] ?? false) ? 'rgba(220,38,38,.9)' : '#4b5563' }};">
            <span class="live-dot"></span> {{ ($recognitionStatus['running'] ?? false) ? 'RECOGNITION LIVE' : 'RECOGNITION STOPPED' }}
        </div>
        <form method="POST" action="{{ route('cameras.recognition', $camera) }}">@csrf
            <input type="hidden" name="action" value="{{ ($recognitionStatus['running'] ?? false) ? 'stop' : 'start' }}">
            <button class="btn {{ ($recognitionStatus['running'] ?? false) ? 'btn-danger' : 'btn-success' }}" type="submit"><i class="fa-solid {{ ($recognitionStatus['running'] ?? false) ? 'fa-stop' : 'fa-play' }}"></i> {{ ($recognitionStatus['running'] ?? false) ? 'Stop Recognition' : 'Start Recognition' }}</button>
        </form>
    </div>
    <p class="text-sm text-gray-500">Camera ID: {{ $camera->camera_id }} &bull; Type: {{ ucfirst($camera->stream_type ?? 'unknown') }} &bull; Location: {{ $camera->location }}</p>
</div>

<div class="live-feed-wrapper">
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">Live Camera Feed</h2>
            <span class="badge badge-green">{{ $camera->status->label() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="live-feed-container">
                <div class="live-badge">
                    <span class="live-dot"></span> LIVE
                </div>
                <img
                    src="{{ $streamUrl }}"
                    alt="Live feed from {{ $camera->name }}"
                    id="camera-feed"
                    onerror="this.style.display='none'; document.getElementById('no-signal').style.display='block';"
                >
                <div id="no-signal" class="no-signal" style="display: none;">
                    <i class="fa-solid fa-video-slash text-4xl mb-3 block"></i>
                    <p class="text-base font-medium">No Signal</p>
                    <p class="text-sm mt-1">Unable to connect to Python video server.</p>
                    <p class="text-xs mt-2 text-gray-500 font-mono">{{ $streamUrl }}</p>
                    <button class="btn btn-secondary mt-4 text-xs" onclick="document.getElementById('camera-feed').src='{{ $streamUrl }}?t='+Date.now(); document.getElementById('camera-feed').style.display=''; document.getElementById('no-signal').style.display='none';">
                        <i class="fa-solid fa-rotate"></i> Retry
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-6">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-gray-900">Camera Details</h2>
        </div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Stream Type</span>
                    <span class="detail-value">{{ ucfirst($camera->stream_type ?? '—') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Feed URL</span>
                    <span class="detail-value font-mono text-xs">{{ $streamUrl }}</span>
                </div>
                @if($camera->ip_address)
                <div class="detail-item">
                    <span class="detail-label">IP Address</span>
                    <span class="detail-value font-mono">{{ $camera->ip_address }}:{{ $camera->port }}</span>
                </div>
                @endif
                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="detail-value"><span class="badge badge-green">{{ $camera->status->label() }}</span></span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
