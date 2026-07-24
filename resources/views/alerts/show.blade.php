@extends('layouts.app')
@section('title', 'Alert Detail')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><a href="{{ route('alerts.index') }}">Alerts</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>{{ $alert->alert_id }}</span></nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div><h1 class="text-xl font-semibold text-gray-900">Alert: {{ $alert->alert_id }}</h1><p class="text-sm text-gray-500 mt-1">Alert generated from facial recognition detection.</p></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="card"><div class="card-header flex items-center justify-between"><h2 class="text-sm font-semibold text-gray-900">Alert Information</h2><span class="badge {{ $alert->status->badge() }}">{{ $alert->status->label() }}</span></div>
        <div class="card-body"><div class="detail-grid">
            <div class="detail-item"><span class="detail-label">Alert ID</span><span class="detail-value font-mono">{{ $alert->alert_id }}</span></div>
            <div class="detail-item"><span class="detail-label">Type</span><span class="detail-value"><span class="badge badge-blue">{{ $alert->alert_type ?? 'Unknown' }}</span></span></div>
            <div class="detail-item"><span class="detail-label">Subject</span><span class="detail-value font-medium">{{ $alert->referenceName() }}</span></div>
            <div class="detail-item"><span class="detail-label">Priority</span><span class="detail-value"><span class="badge {{ $alert->priority->badge() }}">{{ $alert->priority->label() }}</span></span></div>
            <div class="detail-item"><span class="detail-label">Status</span><span class="detail-value"><span class="badge {{ $alert->status->badge() }}">{{ $alert->status->label() }}</span></span></div>
            <div class="detail-item"><span class="detail-label">Similarity Score</span><span class="detail-value font-bold {{ $alert->similarity_score >= 80 ? 'text-red-600' : 'text-gray-600' }}">{{ number_format($alert->similarity_score, 1) }}%</span></div>
            <div class="detail-item"><span class="detail-label">Camera</span><span class="detail-value">{{ $alert->camera->name ?? 'N/A' }}</span></div>
            <div class="detail-item"><span class="detail-label">Assigned To</span><span class="detail-value">{{ $alert->assignedTo->name ?? '—' }}</span></div>
            <div class="detail-item"><span class="detail-label">Source Detection</span><span class="detail-value"><a href="{{ route('detections.show', $alert->detection) }}" class="text-navy hover:underline font-mono text-xs">{{ $alert->detection->detection_id ?? 'N/A' }}</a></span></div>
            <div class="detail-item"><span class="detail-label">Created</span><span class="detail-value">{{ $alert->created_at->format('d M Y, h:i A') }}</span></div>
        </div>
        @if($alert->description)
        <div class="mt-4 pt-4 border-t border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Description</h3>
            <p class="text-sm text-gray-700">{{ $alert->description }}</p>
        </div>
        @endif
        </div></div>

        {{-- Timeline --}}
        @if($alert->timeline->isNotEmpty())
        <div class="card"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Alert Timeline</h2></div>
        <div class="card-body"><div class="timeline">
            @foreach($alert->timeline as $event)
            <div class="timeline-item"><div class="timeline-dot blue"></div><div class="ml-2"><p class="text-xs text-gray-400">{{ $event->created_at->format('d M Y, H:i') }}</p><p class="text-sm font-medium text-gray-900">{{ $event->event }}</p><p class="text-sm text-gray-500">{{ $event->description }}</p></div></div>
            @endforeach
        </div></div></div>
        @endif
    </div>

    <div class="space-y-6">
        <div class="card"><div class="card-header"><h2 class="text-sm font-semibold text-gray-900">Actions</h2></div>
        <div class="card-body space-y-2">
            @if($alert->detection)
            <a href="{{ route('detections.show', $alert->detection) }}" class="btn btn-primary w-full"><i class="fa-solid fa-video"></i> View Detection</a>
            @endif
            @if($alert->reference)
            <a href="{{ route('missing-persons.show', $alert->reference) }}" class="btn btn-primary w-full"><i class="fa-solid fa-folder-open"></i> View Case</a>
            @endif
            <form action="{{ route('alerts.send-alert', $alert) }}" method="POST" onsubmit="return confirm('Send alert notification to all patrol units?')">
                @csrf
                <button type="submit" class="btn btn-warning w-full"><i class="fa-solid fa-paper-plane"></i> Send Alert</button>
            </form>
        </div></div>
        <div class="disclaimer"><i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i><p>Alert based on automated facial recognition. All identity matches require human verification. Do not take enforcement action based solely on this alert.</p></div>
    </div>
</div>
@endsection
