@extends('layouts.app')
@section('title', 'Detection Detail')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><a href="{{ route('detections.index') }}">Detections</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>{{ $detection->detection_id }}</span></nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div><h1 class="text-xl font-semibold text-gray-900">Detection: {{ $detection->detection_id }}</h1><p class="text-sm text-gray-500 mt-1">Facial recognition detection record.</p></div>
</div>

<div class="disclaimer mb-6"><i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i><div><p class="font-medium">Important</p><p class="mt-0.5">This similarity score is computer-generated and does not constitute positive identification. Human review and verification are required for identity confirmation.</p></div></div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Image Comparison --}}
        <div class="card"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Image Comparison</h2></div>
        <div class="card-body">
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Captured Image</p>
                    <div class="aspect-square bg-gray-100 rounded border border-gray-200 flex items-center justify-center">
                        @if($detection->captured_image)
                            @php
                                $capturedUrl = $detection->captured_image;
                                if (!str_starts_with($capturedUrl, 'http')) {
                                    $flaskBase = config('services.python_api.base_url', 'http://127.0.0.1:5000');
                                    $capturedUrl = rtrim($flaskBase, '/') . '/outputs/' . ltrim($capturedUrl, '/');
                                }
                            @endphp
                        <img src="{{ $capturedUrl }}" class="w-full h-full object-cover rounded" alt="Captured" onerror="this.src='https://ui-avatars.com/api/?name=No+Image&background=1B3A5C&color=fff&size=300'">
                        @else
                        <img src="https://ui-avatars.com/api/?name=No+Image&background=1B3A5C&color=fff&size=300" class="w-full h-full object-cover rounded" alt="No Image">
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Camera: {{ $detection->camera->name ?? 'N/A' }}<br>{{ $detection->detected_at?->format('d M Y, H:i:s') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Reference Photo</p>
                    <div class="aspect-square bg-gray-100 rounded border border-gray-200 flex items-center justify-center">
                        @php
                            $refPhoto = $detection->reference && $detection->reference->primaryPhoto
                                ? $detection->reference->primaryPhoto->url()
                                : null;
                        @endphp
                        @if($refPhoto)
                        <img src="{{ $refPhoto }}" class="w-full h-full object-cover rounded" alt="Reference" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($detection->referenceName()) }}&background=1B3A5C&color=fff&size=300'">
                        @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($detection->referenceName()) }}&background=1B3A5C&color=fff&size=300" class="w-full h-full object-cover rounded" alt="Reference">
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Reference: {{ $detection->referenceName() }}</p>
                </div>
            </div>
            <div class="mt-6 p-4 bg-gray-50 rounded border border-gray-200 text-center">
                <p class="text-sm text-gray-500">Similarity Score</p>
                <p class="text-4xl font-bold {{ $detection->isHighConfidence() ? 'text-red-600' : 'text-yellow-600' }} mt-1">{{ number_format($detection->similarity_score, 1) }}%</p>
                <p class="text-xs text-gray-500 mt-1">{{ $detection->isHighConfidence() ? 'High Confidence Match' : 'Low Confidence Match' }}</p>
            </div>
        </div></div>

        {{-- Detection Info --}}
        <div class="card"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Detection Information</h2></div>
        <div class="card-body"><div class="detail-grid">
            <div class="detail-item"><span class="detail-label">Detection ID</span><span class="detail-value font-mono">{{ $detection->detection_id }}</span></div>
            <div class="detail-item"><span class="detail-label">Camera</span><span class="detail-value">{{ $detection->camera->name ?? 'N/A' }}</span></div>
            <div class="detail-item"><span class="detail-label">Reference Type</span><span class="detail-value"><span class="badge badge-red">{{ str_replace('_', ' ', ucfirst($detection->reference_type ?? 'Unknown')) }}</span></span></div>
            <div class="detail-item"><span class="detail-label">Reference Name</span><span class="detail-value font-medium">{{ $detection->referenceName() }}</span></div>
            <div class="detail-item"><span class="detail-label">Similarity Score</span><span class="detail-value font-bold {{ $detection->isHighConfidence() ? 'text-red-600' : 'text-gray-600' }}">{{ number_format($detection->similarity_score, 1) }}%</span></div>
            <div class="detail-item"><span class="detail-label">Status</span><span class="detail-value"><span class="badge {{ $detection->status->badge() }}">{{ $detection->status->label() }}</span></span></div>
            <div class="detail-item"><span class="detail-label">Detected At</span><span class="detail-value">{{ $detection->detected_at?->format('d M Y, H:i:s') }}</span></div>
            <div class="detail-item"><span class="detail-label">Reviewed By</span><span class="detail-value">{{ $detection->reviewedBy->name ?? '—' }}</span></div>
            <div class="detail-item"><span class="detail-label">Review Notes</span><span class="detail-value">{{ $detection->review_notes ?? '—' }}</span></div>
        </div></div></div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        <div class="card"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Related</h2></div>
        <div class="card-body space-y-3">
            @if($detection->alerts->isNotEmpty())
            <a href="{{ route('alerts.show', $detection->alerts->first()) }}" class="flex items-center gap-2 text-sm text-navy hover:underline"><i class="fa-solid fa-bell text-gray-400"></i> View Alert</a>
            @else
            <a href="{{ route('alerts.index') }}" class="flex items-center gap-2 text-sm text-navy hover:underline"><i class="fa-solid fa-bell text-gray-400"></i> View Alerts</a>
            @endif
            @if($detection->reference)
            <a href="{{ route('missing-persons.show', $detection->reference) }}" class="flex items-center gap-2 text-sm text-navy hover:underline"><i class="fa-solid fa-magnifying-glass text-gray-400"></i> View Missing Person Profile</a>
            @endif
            <a href="#" class="flex items-center gap-2 text-sm text-navy hover:underline"><i class="fa-solid fa-check-to-slot text-gray-400"></i> Request Verification</a>
        </div></div>
        <div class="disclaimer"><i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i><p>Facial recognition provides investigative leads, not proof of identity. All enforcement actions require verified identification.</p></div>
    </div>
</div>
@endsection
