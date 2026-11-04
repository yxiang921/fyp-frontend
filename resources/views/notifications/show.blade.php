@extends('layouts.app')

@section('title', 'Notification Details')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <a href="{{ route('notifications.index') }}">Notifications</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>Details</span>
</nav>
@endsection

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">{{ $notification->title }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $notification->type->label() }}</p>
        </div>
        <a href="{{ route('notifications.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body space-y-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid {{ $notification->type->icon() }} text-gray-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $notification->message }}</p>
                    <p class="text-xs text-gray-400 mt-3">
                        Received {{ $notification->created_at->format('F j, Y \a\t g:i A') }}
                        &middot; {{ $notification->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>

            @if ($notification->data)
            <div class="border-t border-gray-100 pt-4">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Additional Data</h3>
                <pre class="text-xs text-gray-600 bg-gray-50 rounded p-3 overflow-x-auto">{{ json_encode($notification->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-4 flex justify-end gap-2">
        @if ($notification->isUnread())
        <form action="{{ route('notifications.read', $notification) }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-check"></i> Mark as Read
            </button>
        </form>
        @endif
        <a href="{{ route('notifications.index') }}" class="btn btn-secondary btn-sm">
            Back to Notifications
        </a>
    </div>
</div>
@endsection
