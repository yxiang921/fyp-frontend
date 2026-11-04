<?php

namespace App\Http\Controllers;

use App\Enums\NotificationType;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display the notification listing page.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->notifications()->latest();

        // Filter by type
        if ($request->filled('type') && NotificationType::tryFrom($request->type)) {
            $query->byType(NotificationType::from($request->type));
        }

        // Filter: unread only
        if ($request->boolean('unread')) {
            $query->unread();
        }

        $notifications = $query->paginate(20)->withQueryString();

        // Counts for tab badges
        $counts = [
            'all'        => auth()->user()->notifications()->count(),
            'unread'     => auth()->user()->unreadNotifications()->count(),
        ];

        foreach (NotificationType::cases() as $type) {
            $counts[$type->value] = auth()->user()->notifications()->byType($type)->count();
        }

        $types = NotificationType::cases();

        return view('notifications.index', compact('notifications', 'counts', 'types'));
    }

    /**
     * Show a single notification (or redirect with mark-as-read).
     */
    public function show(Notification $notification)
    {
        $this->authorizeNotification($notification);

        if ($notification->isUnread()) {
            $notification->markAsRead();
        }

        return view('notifications.show', compact('notification'));
    }

    /**
     * Mark a single notification as read (AJAX or form POST).
     */
    public function markAsRead(Notification $notification, Request $request): JsonResponse|RedirectResponse
    {
        $this->authorizeNotification($notification);

        if ($notification->isUnread()) {
            $notification->markAsRead();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok'           => true,
                'unread_count' => auth()->user()->unreadNotifications()->count(),
            ]);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read (AJAX or form POST).
     */
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok'           => true,
                'message'      => 'All notifications marked as read.',
                'unread_count' => 0,
            ]);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'All notifications marked as read.');
    }

    /**
     * Return the current unread count (for header badge polling).
     */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'unread_count' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Poll for new notifications since a given timestamp (for real-time updates).
     */
    public function poll(Request $request): JsonResponse
    {
        $since = $request->input('since');

        if (!$since) {
            return response()->json(['notifications' => [], 'unread_count' => auth()->user()->unreadNotifications()->count()]);
        }

        $notifications = auth()->user()->notifications()
            ->where('created_at', '>', $since)
            ->latest()
            ->take(20)
            ->get()
            ->map(fn (Notification $n) => [
                'id'        => $n->id,
                'type'      => $n->type->value,
                'icon'      => $n->type->icon(),
                'label'     => $n->type->label(),
                'title'     => $n->title,
                'message'   => $n->message,
                'is_unread' => $n->isUnread(),
                'created_at' => $n->created_at->toISOString(),
                'diff'      => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Ensure the user owns this notification.
     */
    private function authorizeNotification(Notification $notification): void
    {
        abort_if($notification->user_id !== auth()->id(), 403);
    }
}
