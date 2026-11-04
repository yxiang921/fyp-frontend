@extends('layouts.app')
@section('title', 'Review Alert')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><a href="{{ route('alerts.index') }}">Alerts</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><a href="{{ route('alerts.show', $alert) }}">{{ $alert->alert_id }}</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>Review</span></nav>
@endsection

@section('content')
<div class="max-w-3xl">
    <h1 class="text-xl font-semibold text-gray-900 mb-6">Review Alert: {{ $alert->alert_id }}</h1>
    <div class="disclaimer mb-6"><i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i><div><p class="font-medium">Review Guidelines</p><p class="mt-0.5">1. Review both images carefully. 2. Note the similarity score is computer-generated. 3. Check subject details. 4. Decide appropriate action. 5. PDRM verification may be required for confirmation.</p></div></div>

    {{-- Comparison --}}
    <div class="card mb-6"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Image Comparison</h2></div>
    <div class="card-body"><div class="grid grid-cols-2 gap-4 mb-4">
        <div class="text-center">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Captured ({{ $alert->camera->name ?? 'N/A' }})</p>
            @php
                $capturedImages = [];
                if ($alert->detection && $alert->detection->captured_image) {
                    $rawImages = explode(',', $alert->detection->captured_image);
                    foreach ($rawImages as $img) {
                        $img = trim($img);
                        if ($img === '') continue;
                        if (!str_starts_with($img, 'http')) {
                            $flaskBase = config('services.python_api.base_url', 'http://127.0.0.1:5000');
                            $img = rtrim($flaskBase, '/') . '/outputs/' . ltrim($img, '/');
                        }
                        $capturedImages[] = $img;
                    }
                }
            @endphp
            <div x-data="{ current: 0, images: {{ json_encode($capturedImages) }} }">
                <div class="image-carousel aspect-square bg-gray-100 rounded border">
                    @if(empty($capturedImages))
                    <img src="https://ui-avatars.com/api/?name=Captured&background=1B3A5C&color=fff&size=200" class="w-full h-full object-cover rounded" alt="No Image">
                    @else
                    <img :src="images[current]" class="w-full h-full object-cover rounded" alt="Captured" onerror="this.src='https://ui-avatars.com/api/?name=Captured&background=1B3A5C&color=fff&size=200'">
                    <button x-show="images.length > 1" @click="current = (current - 1 + images.length) % images.length" class="carousel-btn prev"><i class="fa-solid fa-chevron-left text-xs"></i></button>
                    <button x-show="images.length > 1" @click="current = (current + 1) % images.length" class="carousel-btn next"><i class="fa-solid fa-chevron-right text-xs"></i></button>
                    @endif
                </div>
                <div class="carousel-dots" x-show="images.length > 1">
                    <template x-for="(img, idx) in images" :key="idx">
                        <span class="dot" :class="{ active: idx === current }" @click="current = idx"></span>
                    </template>
                </div>
            </div>
        </div>
        <div class="text-center"><p class="text-xs font-semibold text-gray-500 uppercase mb-2">Reference</p><div class="aspect-square bg-gray-100 rounded border"><img src="https://ui-avatars.com/api/?name={{ urlencode($alert->referenceName()) }}&background=1B3A5C&color=fff&size=200" class="w-full h-full object-cover rounded"></div></div>
    </div>
    <div class="text-center p-4 bg-gray-50 rounded border"><p class="text-sm text-gray-500">Similarity Score</p><p class="text-3xl font-bold {{ $alert->similarity_score >= 80 ? 'text-red-600' : 'text-yellow-600' }}">{{ number_format($alert->similarity_score, 1) }}%</p></div></div></div>

    {{-- Review Form --}}
    <div class="card"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Review Decision</h2></div>
    <div class="card-body">
        <form method="POST" action="#">
            @csrf
            <div class="form-group"><label for="decision" class="form-label">Decision <span class="text-red-500">*</span></label>
                <select id="decision" class="form-select" required><option value="">Select Decision</option><option value="under_review">Mark as Under Review</option><option value="escalated">Escalate</option><option value="false_positive">Mark as False Positive</option><option value="closed">Close Alert</option></select></div>

            <div class="form-group"><label for="assigned_to" class="form-label">Assign To</label>
                <select id="assigned_to" class="form-select">
                    <option value="">Current: {{ $alert->assignedTo->name ?? 'Unassigned' }}</option>
                    @foreach($officers as $officer)
                        <option value="{{ $officer->id }}">{{ $officer->name }}</option>
                    @endforeach
                </select></div>
            <div class="form-group"><label for="notes" class="form-label">Review Notes <span class="text-red-500">*</span></label><textarea id="notes" rows="4" class="form-textarea" placeholder="Document your review findings, observations, and rationale..." required></textarea></div>
            <div class="flex items-center gap-3 mt-6 pt-5 border-t border-gray-200">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Submit Review</button>
                <a href="{{ route('alerts.show', $alert) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div></div>
</div>
@endsection
