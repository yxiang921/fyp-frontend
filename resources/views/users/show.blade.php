@extends('layouts.app')
@section('title', 'View User')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <a href="{{ route('users.index') }}">User Management</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>{{ $user->name }}</span>
</nav>
@endsection

@section('content')
<div class="flex flex-col lg:flex-row gap-6">
    {{-- Left Column: User Details --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold text-gray-900">User Details</h1>
            <div class="flex gap-2">
                <a href="{{ route('users.edit', $user) }}" class="btn btn-secondary">
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                </a>
            </div>
        </div>

        <div class="card mb-6">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-gray-900">Profile Information</h2>
            </div>
            <div class="card-body">
                <div class="flex items-center gap-4 mb-6">
                    <img src="{{ $user->avatarUrl() }}" class="w-16 h-16 rounded-full">
                    <div>
                        <p class="text-lg font-semibold text-gray-900">{{ $user->name }}</p>
                        <span class="badge {{ $user->role->badge() }}">{{ $user->role->label() }}</span>
                    </div>
                </div>

                <div class="detail-grid">
                    <div class="detail-item"><span class="detail-label">Email</span><span class="detail-value">{{ $user->email }}</span></div>
                    <div class="detail-item"><span class="detail-label">Phone</span><span class="detail-value">{{ $user->phone ?? '—' }}</span></div>
                    <div class="detail-item"><span class="detail-label">Agency</span><span class="detail-value">{{ $user->agency ?? '—' }}</span></div>
                    <div class="detail-item"><span class="detail-label">Status</span><span class="detail-value"><span class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></span></div>
                    <div class="detail-item"><span class="detail-label">Created</span><span class="detail-value">{{ $user->created_at->format('d M Y, h:i A') }}</span></div>
                    <div class="detail-item"><span class="detail-label">Last Updated</span><span class="detail-value">{{ $user->updated_at->format('d M Y, h:i A') }}</span></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-gray-900">Activity Summary</h2>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="text-center"><p class="text-2xl font-bold text-gray-900">{{ $user->missingPersons()->count() }}</p><p class="text-xs text-gray-500">Cases Created</p></div>
                    <div class="text-center"><p class="text-2xl font-bold text-gray-900">{{ $user->assignedAlerts()->count() }}</p><p class="text-xs text-gray-500">Alerts Assigned</p></div>
                    <div class="text-center"><p class="text-2xl font-bold text-gray-900">{{ $user->verificationRequests()->count() }}</p><p class="text-xs text-gray-500">Verifications</p></div>
                    <div class="text-center"><p class="text-2xl font-bold text-gray-900">{{ $user->auditLogs()->count() }}</p><p class="text-xs text-gray-500">Audit Entries</p></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: Audit Logs --}}
    <div class="w-full lg:w-96 shrink-0">
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Audit Logs</h2>
                <span class="text-xs text-gray-400">{{ $auditLogs->total() }} entries</span>
            </div>
            <div class="card-body p-0">
                @forelse($auditLogs as $log)
                <div class="px-4 py-3 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between mb-1">
                        <span class="badge badge-blue text-[10px]">{{ $log->action }}</span>
                        <span class="text-[11px] text-gray-400 font-mono">{{ $log->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mb-1">{{ $log->module }}</p>
                    <p class="text-sm text-gray-700 leading-snug line-clamp-2">{{ $log->description }}</p>
                    @if($log->ip_address)
                    <p class="text-[11px] text-gray-400 font-mono mt-1">{{ $log->ip_address }}</p>
                    @endif
                </div>
                @empty
                <div class="px-4 py-8 text-center text-gray-400">
                    <i class="fa-solid fa-clipboard-list text-2xl mb-2 block"></i>
                    <p class="text-sm">No audit logs for this user.</p>
                </div>
                @endforelse
            </div>
            @if($auditLogs->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $auditLogs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
