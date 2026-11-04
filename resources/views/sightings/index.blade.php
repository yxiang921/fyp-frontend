@extends('layouts.app')
@section('title', 'Public Sighting Reports')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>Public Sighting Reports</span></nav>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success mb-4"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Public Sighting Reports</h1>
        <p class="text-sm text-gray-500 mt-1">Review “I’ve seen this person” reports submitted from the mobile app.</p>
    </div>
    <span class="badge {{ $newCount > 0 ? 'badge-blue' : 'badge-gray' }}">{{ $newCount }} new</span>
</div>

<div class="card mb-5">
    <div class="card-body">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <input type="search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Case, person, reporter, phone or location">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
                <a href="{{ route('sightings.index') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Missing Person</th><th>Reporter</th><th>Sighting</th><th>Notes</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @forelse($sightings as $sighting)
                <tr>
                    <td>
                        @if($sighting->missingPerson)
                        <a href="{{ route('missing-persons.show', $sighting->missingPerson) }}" class="text-navy hover:underline font-medium">{{ $sighting->missingPerson->full_name }}</a>
                        <p class="font-mono text-xs text-gray-400">{{ $sighting->missingPerson->case_number }}</p>
                        @else
                        <span class="text-gray-400">Deleted case</span>
                        @endif
                    </td>
                    <td class="text-sm">
                        <p>{{ $sighting->reporter_name ?: 'Anonymous' }}</p>
                        <p class="text-xs text-gray-500">{{ $sighting->reporter_phone ?: 'No phone provided' }}</p>
                    </td>
                    <td class="text-sm">
                        <p class="font-medium text-gray-800">{{ $sighting->location }}</p>
                        <p class="text-xs text-gray-500">{{ $sighting->sighted_at?->format('d M Y, H:i') }}</p>
                    </td>
                    <td class="text-sm text-gray-600 max-w-xs">
                        {{ $sighting->notes ?: '—' }}
                        @foreach($sighting->evidence as $evidence)
                            <p class="mt-2">
                            @if(!$evidence->purged_at && $evidence->expires_at->isFuture())
                                <a class="text-navy underline" href="{{ route('sightings.evidence', $evidence) }}">Evidence {{ $loop->iteration }} ({{ $evidence->mime_type }})</a>
                            @else <span>Evidence expired</span> @endif
                            <small class="block break-all" title="SHA-256">SHA-256: {{ $evidence->sha256 }}</small>
                            <small>Retention until {{ $evidence->expires_at->toDateString() }}</small>
                            </p>
                        @endforeach
                    </td>
                    <td>
                        <span class="badge {{ $sighting->status === \App\Enums\SightingStatus::Confirmed ? 'badge-green' : ($sighting->status === \App\Enums\SightingStatus::Dismissed ? 'badge-gray' : 'badge-blue') }}">{{ $sighting->status->label() }}</span>
                    </td>
                    <td class="text-right">
                        <a href="{{ route('sightings.show', $sighting) }}" class="btn btn-sm btn-primary"><i class="fa-solid fa-eye"></i> View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-gray-400 py-8">No public sighting reports found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sightings->hasPages())
    <div class="card-body border-t border-gray-100">{{ $sightings->links() }}</div>
    @endif
</div>
@endsection
