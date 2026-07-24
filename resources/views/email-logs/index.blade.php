@extends('layouts.app')
@section('title', 'Email Logs')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>Email Logs</span></nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Email Logs</h1>
        <p class="text-sm text-gray-500 mt-1">Track all email notifications sent by the system, including auto-generated alerts and manual dispatches.</p>
    </div>
</div>

{{-- Stats summary --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    @php
        $totalCount = \App\Models\EmailLog::count();
        $sentCount = \App\Models\EmailLog::where('status', 'sent')->count();
        $failedCount = \App\Models\EmailLog::where('status', 'failed')->count();
    @endphp
    <div class="stat-card">
        <div class="flex items-center gap-3">
            <div class="stat-icon bg-blue-100 text-blue-600"><i class="fa-solid fa-envelopes-bulk"></i></div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $totalCount }}</p>
                <p class="text-sm text-gray-500">Total Emails</p>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center gap-3">
            <div class="stat-icon bg-green-100 text-green-600"><i class="fa-solid fa-circle-check"></i></div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $sentCount }}</p>
                <p class="text-sm text-gray-500">Sent Successfully</p>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center gap-3">
            <div class="stat-icon bg-red-100 text-red-600"><i class="fa-solid fa-circle-exclamation"></i></div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $failedCount }}</p>
                <p class="text-sm text-gray-500">Failed</p>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('email-logs.index') }}" class="flex gap-2 flex-wrap flex-1">
        <input type="search" name="search" placeholder="Search recipient, subject, or log ID..." class="form-input" value="{{ request('search') }}">
        <select name="status" class="form-select w-auto">
            <option value="">All Statuses</option>
            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
            <option value="queued" {{ request('status') == 'queued' ? 'selected' : '' }}>Queued</option>
            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
        </select>
        <select name="trigger_type" class="form-select w-auto">
            <option value="">All Triggers</option>
            <option value="auto" {{ request('trigger_type') == 'auto' ? 'selected' : '' }}>Auto (System)</option>
            <option value="manual" {{ request('trigger_type') == 'manual' ? 'selected' : '' }}>Manual (Send Alert)</option>
        </select>
        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        @if(request()->anyFilled(['search', 'status', 'trigger_type']))
        <a href="{{ route('email-logs.index') }}" class="btn btn-sm btn-secondary"><i class="fa-solid fa-xmark"></i> Clear</a>
        @endif
    </form>
</div>

{{-- Email logs table --}}
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Log ID</th>
                <th>Recipient</th>
                <th>Subject</th>
                <th>Type</th>
                <th>Alert</th>
                <th>Trigger</th>
                <th>Status</th>
                <th>Sent At</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($emailLogs as $log)
            <tr>
                <td>
                    <a href="{{ route('email-logs.show', $log) }}" class="text-navy hover:underline font-mono text-xs">
                        {{ $log->email_log_id }}
                    </a>
                </td>
                <td>
                    <div class="text-sm font-medium text-gray-900">{{ $log->recipient_name ?? '—' }}</div>
                    <div class="text-xs text-gray-500 font-mono">{{ $log->recipient_email }}</div>
                </td>
                <td class="text-sm text-gray-700 max-w-xs truncate">{{ $log->subject }}</td>
                <td><span class="badge badge-blue">{{ $log->mailableLabel() }}</span></td>
                <td>
                    @if($log->alert)
                    <a href="{{ route('alerts.show', $log->alert) }}" class="text-navy hover:underline font-mono text-xs">
                        {{ $log->alert->alert_id }}
                    </a>
                    @else
                    <span class="text-xs text-gray-400">—</span>
                    @endif
                </td>
                <td><span class="badge {{ $log->trigger_type == 'auto' ? 'badge-purple' : 'badge-teal' }}">{{ $log->triggerTypeLabel() }}</span></td>
                <td><span class="badge {{ $log->statusBadge() }}">{{ $log->statusLabel() }}</span></td>
                <td class="text-xs text-gray-500 font-mono whitespace-nowrap">
                    {{ $log->sent_at ? $log->sent_at->format('Y-m-d H:i:s') : '—' }}
                </td>
                <td class="text-right">
                    <a href="{{ route('email-logs.show', $log) }}" class="btn btn-sm btn-secondary" title="View details">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-gray-400 py-8">
                    <i class="fa-solid fa-envelope text-2xl mb-2 block"></i>
                    No email logs recorded yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($emailLogs->hasPages())
    <div class="pagination-container">
        <p class="text-sm text-gray-500">Showing {{ $emailLogs->firstItem() }} to {{ $emailLogs->lastItem() }} of {{ $emailLogs->total() }} entries</p>
        <div class="flex items-center gap-1">{{ $emailLogs->links() }}</div>
    </div>
    @endif
</div>
@endsection
