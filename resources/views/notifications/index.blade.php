@extends('layouts.app')
@section('title', 'Notifications')

@section('breadcrumbs')
<nav class="breadcrumb"><a href="{{ route('dashboard') }}">Dashboard</a><span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span><span>Notifications</span></nav>
@endsection

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-xl font-semibold text-gray-900">Notifications</h1><p class="text-sm text-gray-500 mt-1">System notifications and updates.</p></div>
        <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-check-double"></i> Mark All Read</button>
    </div>

    <div class="card">
        <div class="divide-y divide-gray-100">
            @php $notifications = auth()->user()->notifications()->latest()->paginate(15); @endphp
            @forelse($notifications as $notification)
            <div class="px-5 py-4 flex items-start gap-3 {{ $notification->isUnread() ? 'bg-blue-50/50' : '' }}">
                @php
                    $iconMap = [
                        'alert_new' => 'fa-bell text-red-500',
                        'alert_updated' => 'fa-bell text-orange-500',
                        'verification_completed' => 'fa-check-double text-green-500',
                        'detection_new' => 'fa-camera text-purple-500',
                        'case_update' => 'fa-folder-open text-gray-500',
                    ];
                    $icon = $iconMap[$notification->type] ?? 'fa-circle-info text-gray-400';
                @endphp
                <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid {{ $icon }}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                        @if($notification->isUnread())<span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-1.5"></span>@endif
                    </div>
                    <p class="text-sm text-gray-600 mt-0.5">{{ $notification->message }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <div class="px-5 py-12 text-center">
                <i class="fa-regular fa-bell text-3xl text-gray-300 mb-3 block"></i>
                <p class="text-sm text-gray-500">No notifications yet.</p>
            </div>
            @endforelse
        </div>
        @if($notifications->hasPages())
        <div class="pagination-container">
            <p class="text-sm text-gray-500">Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} of {{ $notifications->total() }} notifications</p>
            <div class="flex items-center gap-1">{{ $notifications->links() }}</div>
        </div>
        @endif
    </div>
</div>
@endsection
