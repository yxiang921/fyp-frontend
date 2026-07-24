@extends('layouts.app')
@section('title', 'My Dashboard')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>My Dashboard</span>
</nav>
@endsection

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-semibold text-gray-900">My Dashboard</h1>
    <p class="text-sm text-gray-500 mt-1">Track your missing person reports and case statuses.</p>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-icon bg-blue-50 text-blue-700">
            <i class="fa-solid fa-file-lines"></i>
        </div>
        <div class="stat-value">{{ $stats['totalCases'] }}</div>
        <div class="stat-label">Total Submitted</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-yellow-50 text-yellow-700">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="stat-value">{{ $stats['pendingCases'] }}</div>
        <div class="stat-label">Pending Verification</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-red-50 text-red-700">
            <i class="fa-solid fa-search"></i>
        </div>
        <div class="stat-value">{{ $stats['activeCases'] }}</div>
        <div class="stat-label">Active (Monitoring)</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green-50 text-green-700">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="stat-value">{{ $stats['resolvedCases'] }}</div>
        <div class="stat-label">Resolved</div>
    </div>
</div>

{{-- My Cases --}}
<div class="card mb-6">
    <div class="card-header">
        <h2 class="text-sm font-semibold text-gray-900">My Submitted Cases</h2>
        <a href="{{ route('public.cases') }}" class="text-xs text-navy hover:underline">View all</a>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Case Number</th>
                    <th>Name</th>
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
                    <td colspan="6" class="text-center text-gray-400 py-8">
                        <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i>
                        You haven't submitted any missing person reports yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Quick Actions --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <a href="{{ route('public.report-missing') }}" class="card p-6 hover:bg-gray-50 transition-colors text-center">
        <i class="fa-solid fa-paper-plane text-3xl text-navy mb-3"></i>
        <p class="text-base font-semibold text-gray-900">Report Missing Person</p>
        <p class="text-sm text-gray-500 mt-1">Submit a new missing person report for verification and monitoring.</p>
    </a>
    <a href="{{ route('public.cases') }}" class="card p-6 hover:bg-gray-50 transition-colors text-center">
        <i class="fa-solid fa-list-check text-3xl text-navy mb-3"></i>
        <p class="text-base font-semibold text-gray-900">View All My Cases</p>
        <p class="text-sm text-gray-500 mt-1">Track status and updates for all your submitted cases.</p>
    </a>
</div>
@endsection
