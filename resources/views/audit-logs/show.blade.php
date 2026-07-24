@extends('layouts.app')
@section('title', 'Audit Log Detail')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><a href="{{ route('audit-logs.index') }}">Audit Logs</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>Log Detail</span></nav>
@endsection

@section('content')
<div class="max-w-3xl">
    <h1 class="text-xl font-semibold text-gray-900 mb-6">Audit Log Detail</h1>
    <div class="card"><div class="card-body"><div class="detail-grid">
        <div class="detail-item"><span class="detail-label">Timestamp</span><span class="detail-value font-mono text-sm">{{ $auditLog->created_at->format('Y-m-d H:i:s') }}</span></div>
        <div class="detail-item"><span class="detail-label">User</span><span class="detail-value">{{ $auditLog->user->name ?? 'System' }}</span></div>
        <div class="detail-item"><span class="detail-label">Action</span><span class="detail-value"><span class="badge badge-blue">{{ $auditLog->action }}</span></span></div>
        <div class="detail-item"><span class="detail-label">Module</span><span class="detail-value">{{ $auditLog->module }}</span></div>
        <div class="detail-item"><span class="detail-label">IP Address</span><span class="detail-value font-mono text-sm">{{ $auditLog->ip_address ?? '—' }}</span></div>
        <div class="detail-item"><span class="detail-label">User Agent</span><span class="detail-value text-sm">{{ $auditLog->user_agent ?? '—' }}</span></div>
    </div>

    <div class="mt-4 pt-4 border-t border-gray-200">
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Description</h3>
        <p class="text-sm text-gray-700">{{ $auditLog->description }}</p>
    </div>

    @if($auditLog->old_values || $auditLog->new_values)
    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        @if($auditLog->old_values)
        <div class="p-3 bg-gray-50 rounded border border-gray-200">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Previous Values</p>
            <pre class="text-xs text-gray-700 font-mono whitespace-pre-wrap">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif
        @if($auditLog->new_values)
        <div class="p-3 bg-gray-50 rounded border border-gray-200">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">New Values</p>
            <pre class="text-xs text-gray-700 font-mono whitespace-pre-wrap">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif
    </div>
    @endif
    </div></div>
</div>
@endsection
