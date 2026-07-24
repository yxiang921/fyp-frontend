@extends('layouts.app')
@section('title', 'Audit Logs')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>Audit Logs</span></nav>
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div><h1 class="text-xl font-semibold text-gray-900">Audit Logs</h1><p class="text-sm text-gray-500 mt-1">System activity and audit trail records.</p></div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead><tr><th>Timestamp</th><th>User</th><th>Action</th><th>Module</th><th>Description</th><th>IP Address</th></tr></thead>
        <tbody>
            @forelse($auditLogs as $log)
            <tr>
                <td class="text-xs text-gray-500 font-mono">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                <td class="text-sm">{{ $log->user->name ?? 'System' }}</td>
                <td><span class="badge badge-blue">{{ $log->action }}</span></td>
                <td class="text-sm">{{ $log->module }}</td>
                <td class="text-sm text-gray-700">{{ $log->description }}</td>
                <td class="text-xs text-gray-500 font-mono">{{ $log->ip_address ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-gray-400 py-8">
                    <i class="fa-solid fa-clipboard-list text-2xl mb-2 block"></i>
                    No audit log entries yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($auditLogs->hasPages())
    <div class="pagination-container">
        <p class="text-sm text-gray-500">Showing {{ $auditLogs->firstItem() }} to {{ $auditLogs->lastItem() }} of {{ $auditLogs->total() }} entries</p>
        <div class="flex items-center gap-1">{{ $auditLogs->links() }}</div>
    </div>
    @endif
</div>
@endsection
