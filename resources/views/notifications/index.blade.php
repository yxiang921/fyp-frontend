@extends('layouts.app')

@section('title', 'Notifications')

@section('breadcrumbs')
<nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="separator"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
    <span>Notifications</span>
</nav>
@endsection

@section('content')
{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Notifications</h1>
        <p class="text-sm text-gray-500 mt-1">System notifications and updates.</p>
    </div>
    <button id="markAllReadBtn" onclick="markAllAsRead()"
        class="btn btn-secondary btn-sm {{ $counts['unread'] === 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
        {{ $counts['unread'] === 0 ? 'disabled' : '' }}>
        <i class="fa-solid fa-check-double"></i> Mark All Read
    </button>
</div>

{{-- Filter Tabs --}}
<div class="flex flex-wrap items-center gap-1.5 mb-5 overflow-x-auto pb-1">
    @php $currentType = request('type'); $currentUnread = request('unread'); @endphp

    <a href="{{ route('notifications.index') }}"
       class="tab-pill {{ !$currentType && !$currentUnread ? 'tab-pill-active' : '' }}">
        All
        <span class="tab-badge">{{ $counts['all'] }}</span>
    </a>
    <a href="{{ route('notifications.index', ['unread' => 1]) }}"
       class="tab-pill {{ $currentUnread ? 'tab-pill-active' : '' }}">
        <i class="fa-solid fa-circle text-[6px] text-blue-500 mr-1"></i> Unread
        <span class="tab-badge {{ $counts['unread'] > 0 ? 'bg-blue-600 text-white' : '' }}">{{ $counts['unread'] }}</span>
    </a>

    @foreach ($types as $type)
    <a href="{{ route('notifications.index', ['type' => $type->value]) }}"
       class="tab-pill {{ $currentType === $type->value ? 'tab-pill-active' : '' }}">
        <i class="fa-solid {{ $type->icon() }} mr-1 text-[11px]"></i> {{ $type->label() }}
        <span class="tab-badge">{{ $counts[$type->value] ?? 0 }}</span>
    </a>
    @endforeach
</div>

{{-- Polling indicator --}}
<div id="polling-indicator" class="text-xs text-gray-400 mb-3 hidden">
    <i class="fa-solid fa-circle-notch animate-spin text-[10px]"></i> Checking for new notifications...
</div>

{{-- Notifications List --}}
<div class="card" id="notification-list">
    <div class="divide-y divide-gray-100" id="notification-container">
        @forelse ($notifications as $notification)
        <div
            class="notification-row px-5 py-4 flex items-start gap-3 transition-colors {{ $notification->isUnread() ? 'bg-blue-50/50 hover:bg-blue-50' : 'hover:bg-gray-50' }}"
            data-id="{{ $notification->id }}"
            data-unread="{{ $notification->isUnread() ? '1' : '0' }}"
        >
            {{-- Icon --}}
            <div class="notif-icon-wrap w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0
                {{ $notification->isUnread() ? 'bg-blue-100' : 'bg-gray-100' }}">
                <i class="fa-solid {{ $notification->type->icon() }}
                   {{ $notification->isUnread() ? 'text-blue-600' : 'text-gray-400' }}"></i>
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                    <span class="flex items-center gap-2 flex-shrink-0">
                        @if ($notification->isUnread())
                        <span class="notif-dot w-2 h-2 rounded-full bg-blue-500" title="Unread"></span>
                        <button
                            data-action="mark-read"
                            data-notif-id="{{ $notification->id }}"
                            class="mark-read-btn text-xs text-gray-400 hover:text-navy transition-colors"
                            title="Mark as read">
                            <i class="fa-solid fa-check"></i>
                        </button>
                        @endif
                    </span>
                </div>

                <p class="text-sm text-gray-600 mt-0.5">{{ $notification->message }}</p>

                <div class="flex items-center gap-3 mt-1.5">
                    <span class="text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-500 font-medium">
                        {{ $notification->type->label() }}
                    </span>
                </div>
            </div>
        </div>
        @empty
        <div class="px-5 py-16 text-center" id="empty-state">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                <i class="fa-regular fa-bell text-2xl text-gray-300"></i>
            </div>
            <p class="text-sm font-medium text-gray-500">No notifications yet</p>
            <p class="text-xs text-gray-400 mt-1">Notifications from system events will appear here.</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($notifications->hasPages())
    <div class="pagination-container">
        <p class="text-sm text-gray-500">
            Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} of {{ $notifications->total() }} notifications
        </p>
        <div class="flex items-center gap-1">{{ $notifications->links() }}</div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// --- Real-time notification page logic ---
(function() {
    let unreadCount = {{ $counts['unread'] }};
    let lastPoll = '{{ now()->toISOString() }}';
    let pollTimer = null;
    const POLL_INTERVAL = 30000; // 30 seconds

    // --- Delegate click for mark-read buttons ---
    document.getElementById('notification-list').addEventListener('click', async function(event) {
        const btn = event.target.closest('[data-action="mark-read"]');
        if (!btn) return;
        const id = parseInt(btn.getAttribute('data-notif-id'), 10);
        await markAsRead(btn, id);
    });

    // --- Mark a single notification as read ---
    async function markAsRead(btn, id) {
        const row = btn.closest('.notification-row');
        if (!row) return;

        try {
            const res = await fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            if (data.ok) {
                markRowRead(row);
                unreadCount = data.unread_count;
                updateUIState(data.unread_count);
            }
        } catch (e) {
            console.error('Failed to mark notification as read:', e);
        }
    }

    // --- Visual helper: turn a row from unread -> read state ---
    function markRowRead(row) {
        row.setAttribute('data-unread', '0');
        row.classList.remove('bg-blue-50/50', 'hover:bg-blue-50');
        row.classList.add('hover:bg-gray-50');

        const icon = row.querySelector('.notif-icon-wrap .fa-solid');
        if (icon) {
            icon.classList.remove('text-blue-600');
            icon.classList.add('text-gray-400');
        }
        const iconBg = row.querySelector('.notif-icon-wrap');
        if (iconBg) {
            iconBg.classList.remove('bg-blue-100');
            iconBg.classList.add('bg-gray-100');
        }
        const dot = row.querySelector('.notif-dot');
        if (dot) dot.remove();
        const markBtn = row.querySelector('.mark-read-btn');
        if (markBtn) markBtn.remove();
    }

    // --- Mark all as read ---
    window.markAllAsRead = async function() {
        if (unreadCount === 0) return;
        try {
            const res = await fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            if (data.ok) {
                document.querySelectorAll('.notification-row[data-unread="1"]').forEach(row => {
                    markRowRead(row);
                });
                unreadCount = 0;
                updateUIState(0);
            }
        } catch (e) {
            console.error('Failed to mark all as read:', e);
        }
    };

    // --- Update button state and badge ---
    function updateUIState(count) {
        const btn = document.getElementById('markAllReadBtn');
        const badge = document.getElementById('header-notif-badge');

        if (count === 0) {
            if (btn) { btn.classList.add('opacity-50', 'cursor-not-allowed'); btn.disabled = true; }
        } else {
            if (btn) { btn.classList.remove('opacity-50', 'cursor-not-allowed'); btn.disabled = false; }
        }

        if (badge) {
            if (count > 0) {
                badge.textContent = count > 9 ? '9+' : count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    // --- Poll for new notifications ---
    function startPolling() {
        pollTimer = setInterval(pollForNew, POLL_INTERVAL);
    }

    async function pollForNew() {
        const indicator = document.getElementById('polling-indicator');
        if (indicator) indicator.classList.remove('hidden');

        try {
            const res = await fetch(`/notifications/api/poll?since=${encodeURIComponent(lastPoll)}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await res.json();

            unreadCount = data.unread_count;
            updateUIState(data.unread_count);
            lastPoll = new Date().toISOString();

            if (data.notifications && data.notifications.length > 0) {
                prependNotifications(data.notifications);
            }
        } catch (e) {
            // Silently fail
        } finally {
            if (indicator) indicator.classList.add('hidden');
        }
    }

    // --- Prepend new notifications ---
    function prependNotifications(notifs) {
        const container = document.getElementById('notification-container');
        if (!container) return;

        const emptyState = document.getElementById('empty-state');
        if (emptyState) emptyState.remove();

        notifs.reverse().forEach(n => {
            const el = document.createElement('div');
            el.className = 'notification-row px-5 py-4 flex items-start gap-3 transition-colors ' +
                (n.is_unread ? 'bg-blue-50/50 hover:bg-blue-50' : 'hover:bg-gray-50');
            el.setAttribute('data-id', n.id);
            el.setAttribute('data-unread', n.is_unread ? '1' : '0');

            const checkBtn = n.is_unread
                ? `<button data-action="mark-read" data-notif-id="${n.id}" class="mark-read-btn text-xs text-gray-400 hover:text-navy transition-colors" title="Mark as read"><i class="fa-solid fa-check"></i></button>`
                : '';
            const dotHtml = n.is_unread
                ? '<span class="notif-dot w-2 h-2 rounded-full bg-blue-500" title="Unread"></span>'
                : '';
            const iconColorClass = n.is_unread ? 'text-blue-600' : 'text-gray-400';
            const iconBgClass = n.is_unread ? 'bg-blue-100' : 'bg-gray-100';

            el.innerHTML = `
                <div class="notif-icon-wrap w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 ${iconBgClass}">
                    <i class="fa-solid ${escapeHtml(n.icon)} ${iconColorClass}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-medium text-gray-900">${escapeHtml(n.title)}</p>
                        <span class="flex items-center gap-2 flex-shrink-0">${dotHtml}${checkBtn}</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-0.5">${escapeHtml(n.message)}</p>
                    <div class="flex items-center gap-3 mt-1.5">
                        <span class="text-xs text-gray-400">${escapeHtml(n.diff)}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-500 font-medium">${escapeHtml(n.label)}</span>
                    </div>
                </div>
            `;
            container.insertBefore(el, container.firstChild);

            // Flash highlight
            el.style.backgroundColor = '#eff6ff';
            setTimeout(() => { el.style.backgroundColor = ''; }, 2000);
        });
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // --- Start ---
    startPolling();
})();
</script>
@endpush
