@extends('layouts.public')
@section('title', $missingPerson->full_name . ' — Missing Person')

@section('content')
<a class="btn btn-primary mb-4" href="{{ route('public.sighting', $missingPerson) }}">{{ __("I've seen this person") }}</a>
{{-- Back link --}}
<a href="{{ route('public.gallery.index') }}" class="inline-flex items-center gap-1 text-sm text-navy hover:text-navy-light mb-6 transition-colors">
    <i class="fa-solid fa-arrow-left text-xs"></i> Back to Gallery
</a>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Photos --}}
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-gray-900">Photos</h2>
            </div>
            <div class="card-body">
                @if($missingPerson->photos->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($missingPerson->photos as $photo)
                    <div class="aspect-square bg-gray-100 rounded overflow-hidden">
                        <img src="{{ $photo->url() }}"
                             alt="{{ $missingPerson->full_name }} — {{ $photo->type->label() }}"
                             class="w-full h-full object-cover"
                             loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-400 text-center py-12">No photos available.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="card h-fit">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-gray-900">Case Details</h2>
        </div>
        <div class="card-body space-y-4">
            <div>
                <span class="detail-label">Full Name</span>
                <span class="detail-value text-base font-semibold">{{ $missingPerson->full_name }}</span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="detail-item">
                    <span class="detail-label">Case Number</span>
                    <span class="detail-value font-mono text-xs">{{ $missingPerson->case_number }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="badge {{ $missingPerson->status->value === 'active' ? 'badge-red' : ($missingPerson->status->value === 'found' ? 'badge-green' : 'badge-gray') }}">
                        {{ $missingPerson->status->label() }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="detail-item">
                    <span class="detail-label">Gender</span>
                    <span class="detail-value">{{ ucfirst($missingPerson->gender) }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Date of Birth</span>
                    <span class="detail-value">{{ $missingPerson->date_of_birth?->format('d M Y') ?? '—' }}</span>
                </div>
            </div>

            <div class="detail-item">
                <span class="detail-label">Last Seen Location</span>
                <span class="detail-value">{{ $missingPerson->last_seen_location ?? '—' }}</span>
            </div>

            <div class="detail-item">
                <span class="detail-label">Last Seen Date</span>
                <span class="detail-value">{{ $missingPerson->last_seen_date?->format('d M Y') ?? '—' }}</span>
            </div>

            @if($missingPerson->description)
            <div class="detail-item">
                <span class="detail-label">Description</span>
                <p class="text-sm text-gray-700 mt-1 leading-relaxed">{{ $missingPerson->description }}</p>
            </div>
            @endif
        </div>

        @if($missingPerson->status->value === 'active')
        <div class="card-footer">
            <p class="text-xs text-gray-500 flex items-start gap-2">
                <i class="fa-solid fa-circle-info mt-0.5"></i>
                If you have any information about this person, please contact your local police station immediately and reference case number <strong class="text-gray-700">{{ $missingPerson->case_number }}</strong>.
            </p>
        </div>
        @endif
    </div>
</div>
@endsection
