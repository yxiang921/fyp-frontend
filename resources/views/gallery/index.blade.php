@extends('layouts.public')
@section('title', 'Missing Persons Gallery')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-semibold text-gray-900">Missing Persons Gallery</h1>
    <p class="text-sm text-gray-500 mt-1">Browse all reported missing persons. If you have any information, please contact the authorities.</p>
</div>

@if($missingPersons->isEmpty())
<div class="empty-state py-20">
    <i class="fa-solid fa-images"></i>
    <h3>No Records Yet</h3>
    <p>There are no missing person reports with photos at this time.</p>
</div>
@else
    {{-- Responsive Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
        @foreach($missingPersons as $person)
        <a href="{{ route('public.gallery.show', $person) }}"
           class="group block bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-navy focus:ring-offset-2">
            {{-- Photo --}}
            <div class="relative aspect-square bg-gray-100 overflow-hidden">
                @if($person->primaryPhoto)
                    <img src="{{ $person->primaryPhoto->url() }}"
                         alt="{{ $person->full_name }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                         loading="lazy">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                        <i class="fa-solid fa-user text-4xl"></i>
                    </div>
                @endif

                {{-- Status badge overlay --}}
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="badge {{ $person->status->value === 'active' ? 'badge-red' : ($person->status->value === 'found' ? 'badge-green' : 'badge-gray') }} text-[10px]">
                        {{ $person->status->label() }}
                    </span>
                </div>
            </div>

            {{-- Info --}}
            <div class="p-2 sm:p-3">
                <p class="text-xs sm:text-sm font-medium text-gray-900 truncate">{{ $person->full_name }}</p>
                <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5">
                    <span class="inline-flex items-center gap-1">
                        <i class="fa-solid fa-location-dot text-[10px] text-gray-400"></i>
                        <span class="truncate">{{ \Illuminate\Support\Str::limit($person->last_seen_location, 20) }}</span>
                    </span>
                </p>
                <p class="text-[10px] text-gray-400 mt-1">{{ $person->last_seen_date?->format('d M Y') }}</p>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($missingPersons->hasPages())
    <div class="mt-8">
        {{ $missingPersons->links() }}
    </div>
    @endif
@endif
@endsection
