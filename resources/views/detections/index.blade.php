@extends('layouts.app')
@section('title', 'Detections')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>Detections</span></nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div><h1 class="text-xl font-semibold text-gray-900">Detections</h1><p class="text-sm text-gray-500 mt-1">Facial recognition detection records.</p></div>
</div>

<div class="disclaimer mb-4">
    <i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i>
    <div><p class="font-medium">Important Disclaimer</p><p class="mt-0.5">Facial recognition results are NOT final identification. Similarity scores indicate potential matches only. All identity confirmations require human review and PDRM verification.</p></div>
</div>

<div class="filter-bar">
    <div class="flex-1 min-w-[200px]">
        <form method="GET" action="{{ route('detections.index') }}" class="flex gap-2">
            <input type="search" name="search" placeholder="Search detection ID..." class="form-input" value="{{ request('search') }}">
            <select name="camera_id" class="form-select w-auto">
                <option value="">All Cameras</option>
                @foreach($cameras as $camera)
                    <option value="{{ $camera->id }}" {{ request('camera_id') == $camera->id ? 'selected' : '' }}>{{ $camera->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select w-auto">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        </form>
    </div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead><tr><th>Detection ID</th><th>Camera</th><th>Missing Person</th><th>Score</th><th>Status</th><th>Date/Time</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
            @forelse($detections as $detection)
            <tr>
                <td><a href="{{ route('detections.show', $detection) }}" class="text-navy hover:underline font-mono text-xs">{{ $detection->detection_id }}</a></td>
                <td><div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full {{ $detection->camera?->status?->value === 'online' ? 'bg-green-500' : 'bg-gray-400' }}"></span>{{ $detection->camera->name ?? 'N/A' }}</div></td>
                <td class="text-sm">{{ $detection->referenceName() }}</td>
                <td><span class="text-sm {{ $detection->isHighConfidence() ? 'text-red-600 font-semibold' : 'text-gray-600' }}">{{ number_format($detection->similarity_score, 1) }}%</span></td>
                <td><span class="badge {{ $detection->status->badge() }}">{{ $detection->status->label() }}</span></td>
                <td class="text-gray-500 text-xs">{{ $detection->detected_at?->format('d M Y H:i') }}</td>
                <td class="text-right"><a href="{{ route('detections.show', $detection) }}" class="btn btn-sm btn-secondary"><i class="fa-solid fa-eye"></i></a></td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-gray-400 py-8">
                    <i class="fa-solid fa-camera-retro text-2xl mb-2 block"></i>
                    No detections recorded yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($detections->hasPages())
    <div class="pagination-container">
        <p class="text-sm text-gray-500">Showing {{ $detections->firstItem() }} to {{ $detections->lastItem() }} of {{ $detections->total() }} entries</p>
        <div class="flex items-center gap-1">{{ $detections->links() }}</div>
    </div>
    @endif
</div>
@endsection
