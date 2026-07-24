@extends('layouts.app')
@section('title', 'Verification Detail')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><a href="{{ route('verifications.index') }}">Verifications</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>{{ $verification->request_id }}</span></nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div><h1 class="text-xl font-semibold text-gray-900">Verification Request: {{ $verification->request_id }}</h1><p class="text-sm text-gray-500 mt-1">Identity verification via PDRM records.</p></div>
    @if(in_array(auth()->user()->role->value, ['super_admin', 'pdrm_officer']))
    <a href="{{ route('verifications.review', $verification) }}" class="btn btn-primary"><i class="fa-solid fa-clipboard-check"></i> Review</a>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="card"><div class="card-header flex items-center justify-between"><h2 class="text-sm font-semibold text-gray-900">Request Information</h2><span class="badge {{ $verification->status->badge() }}">{{ $verification->status->label() }}</span></div>
        <div class="card-body"><div class="detail-grid">
            <div class="detail-item"><span class="detail-label">Request ID</span><span class="detail-value font-mono">{{ $verification->request_id }}</span></div>
            <div class="detail-item"><span class="detail-label">Status</span><span class="detail-value"><span class="badge {{ $verification->status->badge() }}">{{ $verification->status->label() }}</span></span></div>
            <div class="detail-item"><span class="detail-label">Detection</span><span class="detail-value"><a href="{{ route('detections.show', $verification->detection) }}" class="text-navy hover:underline font-mono text-xs">{{ $verification->detection->detection_id ?? 'N/A' }}</a></span></div>
            <div class="detail-item"><span class="detail-label">Alert</span><span class="detail-value">
                @if($verification->alert)
                <a href="{{ route('alerts.show', $verification->alert) }}" class="text-navy hover:underline font-mono text-xs">{{ $verification->alert->alert_id }}</a>
                @else
                <span class="text-gray-400">—</span>
                @endif
            </span></div>
            <div class="detail-item"><span class="detail-label">Requested By</span><span class="detail-value">{{ $verification->requester->name ?? 'N/A' }} {{ $verification->requester->agency ? '(' . $verification->requester->agency . ')' : '' }}</span></div>
            <div class="detail-item"><span class="detail-label">Request Date</span><span class="detail-value">{{ $verification->created_at->format('d M Y, H:i') }}</span></div>
            <div class="detail-item"><span class="detail-label">Subject</span><span class="detail-value font-medium">{{ $verification->detection?->referenceName() ?? 'N/A' }}</span></div>
            @if($verification->reviewed_by)
            <div class="detail-item"><span class="detail-label">Reviewed By</span><span class="detail-value">{{ $verification->reviewer->name ?? 'N/A' }}</span></div>
            <div class="detail-item"><span class="detail-label">Verified At</span><span class="detail-value">{{ $verification->verified_at?->format('d M Y, H:i') }}</span></div>
            @endif
        </div></div></div>

        @if($verification->detection)
        {{-- Image Comparison --}}
        <div class="card"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Image Comparison</h2></div>
        <div class="card-body">
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Captured Image</p>
                    <div class="aspect-square bg-gray-100 rounded border border-gray-200 flex items-center justify-center">
                        @if($verification->detection->captured_image)
                            @php
                                $capturedUrl = $verification->detection->captured_image;
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
                    <p class="text-xs text-gray-500 mt-2">Camera: {{ $verification->detection->camera->name ?? 'N/A' }}<br>{{ $verification->detection->detected_at?->format('d M Y, H:i:s') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Reference Photo</p>
                    <div class="aspect-square bg-gray-100 rounded border border-gray-200 flex items-center justify-center">
                        @php
                            $refPhoto = null;
                            $refName = $verification->detection->referenceName();
                            if ($verification->detection->reference && $verification->detection->reference->primaryPhoto) {
                                $refPhoto = $verification->detection->reference->primaryPhoto->url();
                            }
                        @endphp
                        @if($refPhoto)
                        <img src="{{ $refPhoto }}" class="w-full h-full object-cover rounded" alt="Reference" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($refName) }}&background=1B3A5C&color=fff&size=300'">
                        @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($refName) }}&background=1B3A5C&color=fff&size=300" class="w-full h-full object-cover rounded" alt="Reference">
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Reference: {{ $refName }}</p>
                </div>
            </div>
            <div class="mt-6 p-4 bg-gray-50 rounded border border-gray-200 text-center">
                <p class="text-sm text-gray-500">Similarity Score</p>
                <p class="text-4xl font-bold {{ ($verification->detection->similarity_score ?? 0) >= 80 ? 'text-red-600' : 'text-yellow-600' }} mt-1">{{ number_format($verification->detection->similarity_score ?? 0, 1) }}%</p>
            </div>
        </div></div>
        @endif

        @if($verification->police_notes)
        <div class="card"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Police Notes</h2></div>
        <div class="card-body"><p class="text-sm text-gray-700">{{ $verification->police_notes }}</p></div></div>
        @endif

        @if($verification->jpn_notes)
        <div class="card"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">PDRM Review Notes</h2></div>
        <div class="card-body"><p class="text-sm text-gray-700">{{ $verification->jpn_notes }}</p></div></div>
        @endif
    </div>

    <div class="space-y-6">
        <div class="card"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Verification Status</h2></div>
        <div class="card-body text-center py-6">
            @if($verification->status->value === 'pending')
                <i class="fa-regular fa-clock text-4xl text-yellow-400 mb-3"></i>
                <p class="text-sm font-medium text-gray-700">Awaiting PDRM Review</p>
                <p class="text-xs text-gray-500 mt-1">This request has been submitted and is pending review by a PDRM officer.</p>
            @elseif($verification->status->value === 'confirmed')
                <i class="fa-solid fa-circle-check text-4xl text-green-500 mb-3"></i>
                <p class="text-sm font-medium text-gray-700">Identity Confirmed</p>
                <p class="text-xs text-gray-500 mt-1">Verified by {{ $verification->reviewer->name ?? 'N/A' }} on {{ $verification->verified_at?->format('d M Y') }}.</p>
            @elseif($verification->status->value === 'rejected')
                <i class="fa-solid fa-circle-xmark text-4xl text-red-500 mb-3"></i>
                <p class="text-sm font-medium text-gray-700">Identity Rejected</p>
                <p class="text-xs text-gray-500 mt-1">Does not match PDRM records.</p>
            @else
                <i class="fa-solid fa-circle-question text-4xl text-gray-400 mb-3"></i>
                <p class="text-sm font-medium text-gray-700">Insufficient Data</p>
                <p class="text-xs text-gray-500 mt-1">More information required for verification.</p>
            @endif
        </div></div>
        <div class="disclaimer"><i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i><p>Identity verification must be confirmed against official PDRM records. A similarity score alone does not constitute proof of identity.</p></div>
    </div>
</div>
@endsection
