@extends('layouts.app')
@section('title', 'Surveillance Cameras')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>Cameras</span>
</nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">IP Cameras</h1>
        <p class="text-sm text-gray-500 mt-1">Manage registered IP surveillance cameras.</p>
    </div>
    <a href="{{ route('cameras.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Register Camera</a>
</div>

@if(session('success'))
<div class="alert alert-success mb-4">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif

<form method="GET" action="{{ route('cameras.index') }}" class="filter-bar">
    <div class="flex-1 min-w-[200px]">
        <input type="search" name="search" placeholder="Search name, ID, location, IP..." class="form-input" value="{{ request('search') }}">
    </div>
    <select name="status" class="form-select w-auto" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>Online</option>
        <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>Offline</option>
        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
    </select>
    <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
</form>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($cameras as $camera)
    <div class="card p-4 hover:bg-gray-50 transition-colors">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-sm font-semibold text-gray-900">{{ $camera->name }}</p>
                <p class="text-xs text-gray-500 font-mono">{{ $camera->camera_id }}</p>
            </div>
            <span class="w-2.5 h-2.5 rounded-full {{ $camera->status->dot() }} flex-shrink-0"></span>
        </div>

        <p class="text-xs text-gray-500 mb-2"><i class="fa-solid fa-location-dot mr-1"></i>{{ $camera->location }}</p>

        @if($camera->hasIpAddress())
        <p class="text-xs text-gray-500 mb-3"><i class="fa-solid fa-network-wired mr-1"></i><code class="font-mono">{{ $camera->ip_address }}:{{ $camera->port }}</code> &middot; {{ strtoupper($camera->stream_type) }}</p>
        @endif

        <div class="flex items-center justify-between text-xs mb-3">
            <span class="text-gray-500">Detections: <span class="font-semibold">{{ number_format($camera->detections()->count()) }}</span></span>
            <span class="text-gray-500">Alerts: <span class="font-semibold">{{ $camera->alerts()->count() }}</span></span>
        </div>

        <div class="flex items-center gap-2">
            <span class="badge {{ $camera->status->badge() }} text-xs">{{ $camera->status->label() }}</span>
            <a href="{{ route('cameras.show', $camera) }}" class="text-xs text-navy hover:underline ml-auto">Details</a>
            @if($camera->hasIpAddress())
            <a href="{{ route('cameras.live', $camera) }}" class="text-xs text-green-600 hover:underline" title="Live View">
                <i class="fa-solid fa-play"></i> Live
            </a>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center text-gray-400 py-8">
        <i class="fa-solid fa-video-slash text-3xl mb-2 block"></i>
        <p>No cameras found.</p>
    </div>
    @endforelse
</div>

@if($cameras->hasPages())
<div class="pagination-container mt-4">
    <p class="text-sm text-gray-500">
        Showing {{ $cameras->firstItem() }} to {{ $cameras->lastItem() }} of {{ $cameras->total() }} cameras
    </p>
    {{ $cameras->links() }}
</div>
@endif
@endsection
