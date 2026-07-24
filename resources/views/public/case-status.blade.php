@extends('layouts.app')
@section('title', 'Case: ' . $missingPerson->case_number)

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <a href="{{ route('public.cases') }}">My Cases</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>{{ $missingPerson->case_number }}</span>
</nav>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success mb-4">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif

<div class="mb-6">
    <h1 class="text-xl font-semibold text-gray-900">{{ $missingPerson->full_name }}</h1>
    <p class="text-sm text-gray-500 mt-1">
        Case Number: <code class="font-mono">{{ $missingPerson->case_number }}</code>
        <span class="mx-2">|</span>
        Status: <span class="badge {{ $missingPerson->status->badge() }}">{{ $missingPerson->status->label() }}</span>
    </p>
</div>

{{-- Status Progress --}}
<div class="card mb-6">
    <div class="card-body">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Case Status</h2>
        <div class="flex items-center gap-0">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $missingPerson->status->value === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                    <i class="fa-solid fa-paper-plane text-sm"></i>
                </div>
                <span class="text-xs font-medium">Submitted</span>
            </div>
            <div class="flex-1 mx-3 h-0.5 {{ $missingPerson->status->value !== 'pending' ? 'bg-green-400' : 'bg-gray-300' }}"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $missingPerson->status->value === 'pending' ? 'bg-gray-100 text-gray-400' : (in_array($missingPerson->status->value, ['active', 'found', 'closed']) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400') }}">
                    <i class="fa-solid fa-check text-sm"></i>
                </div>
                <span class="text-xs font-medium">Verified</span>
            </div>
            <div class="flex-1 mx-3 h-0.5 {{ in_array($missingPerson->status->value, ['found', 'closed']) ? 'bg-green-400' : 'bg-gray-300' }}"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ in_array($missingPerson->status->value, ['found', 'closed']) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                    <i class="fa-solid fa-flag-checkered text-sm"></i>
                </div>
                <span class="text-xs font-medium">Resolved</span>
            </div>
        </div>

        @if($missingPerson->status->value === 'pending')
        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-800">
            <i class="fa-solid fa-clock mr-1"></i> Your case is pending verification by a PDRM officer. Once verified, facial recognition monitoring will begin.
        </div>
        @elseif($missingPerson->status->value === 'active')
        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded text-sm text-blue-800">
            <i class="fa-solid fa-search mr-1"></i> Your case has been verified and is now actively being monitored through facial recognition.
        </div>
        @elseif(in_array($missingPerson->status->value, ['found', 'closed']))
        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded text-sm text-green-800">
            <i class="fa-solid fa-circle-check mr-1"></i> This case has been resolved. Thank you for your report.
        </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Case Details --}}
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-gray-900">Case Details</h2>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Full Name</span><span class="detail-value font-medium">{{ $missingPerson->full_name }}</span></div>
                    <div class="detail-item"><span class="detail-label">Gender</span><span class="detail-value capitalize">{{ $missingPerson->gender }}</span></div>
                    <div class="detail-item"><span class="detail-label">Date of Birth</span><span class="detail-value">{{ $missingPerson->date_of_birth?->format('d M Y') ?? 'Not provided' }}</span></div>
                    <div class="detail-item"><span class="detail-label">National ID</span><span class="detail-value font-mono">{{ $missingPerson->national_id ?? 'Not provided' }}</span></div>
                    <div class="detail-item"><span class="detail-label">Last Seen Date</span><span class="detail-value font-medium text-red-600">{{ $missingPerson->last_seen_date?->format('d M Y') }}</span></div>
                    <div class="detail-item"><span class="detail-label">Last Seen Location</span><span class="detail-value">{{ $missingPerson->last_seen_location }}</span></div>
                    <div class="detail-item"><span class="detail-label">Date Submitted</span><span class="detail-value">{{ $missingPerson->created_at->format('d M Y, H:i') }}</span></div>
                </div>
                @if($missingPerson->description)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Description</h3>
                    <p class="text-sm text-gray-700">{{ $missingPerson->description }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Photos --}}
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-gray-900">Submitted Photos</h2>
            </div>
            <div class="card-body">
                @if($missingPerson->photos->count() > 0)
                <div class="photo-grid">
                    @foreach($missingPerson->photos as $photo)
                    <div class="photo-thumb">
                        <img src="{{ $photo->url() }}" alt="Photo of {{ $missingPerson->full_name }}">
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-400 text-center py-4">No photos uploaded.</p>
                @endif
            </div>
        </div>

        {{-- Timeline --}}
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-gray-900">Case Timeline</h2>
            </div>
            <div class="card-body">
                @if($missingPerson->timeline()->count() > 0)
                <div class="timeline">
                    @foreach($missingPerson->timeline()->latest()->get() as $event)
                    <div class="timeline-item">
                        <div class="timeline-dot {{ match($event->event_type) { 'submitted' => 'yellow', 'verified' => 'green', 'status_changed' => 'blue', default => 'blue' } }}"></div>
                        <div class="ml-2">
                            <p class="text-xs text-gray-400">{{ $event->created_at->format('d M Y H:i') }}</p>
                            <p class="text-sm font-medium text-gray-900">{{ str_replace('_', ' ', ucfirst($event->event_type)) }}</p>
                            <p class="text-sm text-gray-500">{{ $event->description }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-400 text-center py-4">No timeline events yet.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        <div class="card">
            <div class="card-header"><h2 class="text-sm font-semibold text-gray-900">What Happens Next?</h2></div>
            <div class="card-body space-y-3 text-sm text-gray-600">
                <div class="flex gap-2">
                    <span class="text-yellow-600 font-bold">1.</span>
                    <span>A PDRM officer will review your case and verify the information.</span>
                </div>
                <div class="flex gap-2">
                    <span class="text-blue-600 font-bold">2.</span>
                    <span>Once verified, the case becomes active and the system begins facial recognition monitoring.</span>
                </div>
                <div class="flex gap-2">
                    <span class="text-green-600 font-bold">3.</span>
                    <span>If a match is detected, PDRM officers will be alerted for further investigation.</span>
                </div>
                <div class="flex gap-2">
                    <span class="text-purple-600 font-bold">4.</span>
                    <span>You will be notified of any updates to your case status.</span>
                </div>
            </div>
        </div>

        <div class="disclaimer">
            <i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i>
            <p>All submitted information is treated confidentially and used solely for the purpose of locating the missing person.</p>
        </div>
    </div>
</div>
@endsection
