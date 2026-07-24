@extends('layouts.app')
@section('title', 'My Cases')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>My Cases</span>
</nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">My Missing Person Reports</h1>
        <p class="text-sm text-gray-500 mt-1">Track the status of your submitted cases.</p>
    </div>
    <a href="{{ route('public.report-missing') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Report Missing Person
    </a>
</div>

@if(session('success'))
<div class="alert alert-success mb-4">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Case Number</th>
                <th>Full Name</th>
                <th>Gender</th>
                <th>Last Seen</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($myCases as $case)
            <tr>
                <td><code class="text-xs font-mono text-navy">{{ $case->case_number }}</code></td>
                <td class="text-sm font-medium">{{ $case->full_name }}</td>
                <td class="text-sm capitalize">{{ $case->gender }}</td>
                <td class="text-sm text-gray-500">{{ $case->last_seen_date?->format('d M Y') }}</td>
                <td><span class="badge {{ $case->status->badge() }}">{{ $case->status->label() }}</span></td>
                <td class="text-xs text-gray-500">{{ $case->created_at->format('d M Y') }}</td>
                <td>
                    <a href="{{ route('public.case.show', $case) }}" class="btn btn-sm btn-secondary">
                        <i class="fa-solid fa-eye"></i> View
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-gray-400 py-8">
                    <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i>
                    You haven't submitted any missing person reports yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($myCases->hasPages())
    <div class="pagination-container">
        <p class="text-sm text-gray-500">
            Showing {{ $myCases->firstItem() }} to {{ $myCases->lastItem() }} of {{ $myCases->total() }} entries
        </p>
        {{ $myCases->links() }}
    </div>
    @endif
</div>
@endsection
