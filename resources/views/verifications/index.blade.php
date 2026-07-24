@extends('layouts.app')
@section('title', 'Verification Requests')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>Verification Requests</span></nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div><h1 class="text-xl font-semibold text-gray-900">Verification Requests</h1><p class="text-sm text-gray-500 mt-1">Identity verification requests for missing person cases.</p></div>
</div>

<div class="filter-bar">
    <form method="GET" action="{{ route('verifications.index') }}" class="flex gap-2 flex-wrap flex-1">
        <input type="search" name="search" placeholder="Search request ID..." class="form-input" value="{{ request('search') }}">
        <select name="status" class="form-select w-auto">
            <option value="">All Statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
    </form>
</div>

<div class="table-container">
    <table class="data-table">
        <thead><tr><th>Request ID</th><th>Detection</th><th>Alert</th><th>Requested By</th><th>Reviewed By</th><th>Status</th><th>Requested</th><th>Verified</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
            @forelse($verifications as $verification)
            <tr>
                <td><a href="{{ route('verifications.show', $verification) }}" class="text-navy hover:underline font-mono text-xs">{{ $verification->request_id }}</a></td>
                <td><code class="text-xs font-mono">{{ $verification->detection->detection_id ?? 'N/A' }}</code></td>
                <td><code class="text-xs font-mono">{{ $verification->alert->alert_id ?? 'N/A' }}</code></td>
                <td class="text-sm">{{ $verification->requester->name ?? 'N/A' }}</td>
                <td class="text-sm text-gray-500">{{ $verification->reviewer->name ?? '—' }}</td>
                <td><span class="badge {{ $verification->status->badge() }}">{{ $verification->status->label() }}</span></td>
                <td class="text-xs text-gray-500">{{ $verification->created_at->format('d M Y') }}</td>
                <td class="text-xs text-gray-500">{{ $verification->verified_at?->format('d M Y') ?? '—' }}</td>
                <td class="text-right"><div class="flex items-center justify-end gap-1">
                    <a href="{{ route('verifications.show', $verification) }}" class="btn btn-sm btn-secondary"><i class="fa-solid fa-eye"></i></a>
                    @if($verification->status->value === 'pending' && in_array(auth()->user()->role->value, ['super_admin', 'pdrm_officer']))
                    <a href="{{ route('verifications.review', $verification) }}" class="btn btn-sm btn-primary"><i class="fa-solid fa-clipboard-check"></i></a>
                    @endif
                </div></td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-gray-400 py-8">
                    <i class="fa-solid fa-check-to-slot text-2xl mb-2 block"></i>
                    No verification requests yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($verifications->hasPages())
    <div class="pagination-container">
        <p class="text-sm text-gray-500">Showing {{ $verifications->firstItem() }} to {{ $verifications->lastItem() }} of {{ $verifications->total() }} entries</p>
        <div class="flex items-center gap-1">{{ $verifications->links() }}</div>
    </div>
    @endif
</div>
@endsection
