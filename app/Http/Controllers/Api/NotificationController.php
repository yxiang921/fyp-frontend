<?php

namespace App\Http\Controllers\Api;

use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Models\Detection;
use App\Models\MissingPerson;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List the authenticated user's notifications (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->notifications()->latest();

        if ($request->filled('type') && NotificationType::tryFrom($request->type)) {
            $query->byType(NotificationType::from($request->type));
        }

        if ($request->boolean('unread')) {
            $query->unread();
        }

        $notifications = $query->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $notifications->through(fn (Notification $n) => $this->map($n))->items(),
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Return the current unread count (for badge polling).
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        abort_if($notification->user_id !== $request->user()->id, 403);

        if ($notification->isUnread()) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark every notification as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    private function map(Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type->value,
            'title' => $notification->title,
            'message' => $notification->message,
            'data' => $notification->data,
            'is_unread' => $notification->isUnread(),
            'created_at' => $notification->created_at->toIso8601String(),
        ];
    }

    /**
     * Show a single notification with its resolved missing-person and
     * detection detail, for the mobile notification detail page.
     */
    public function show(Request $request, Notification $notification): JsonResponse
    {
        abort_if($notification->user_id !== $request->user()->id, 403);

        $data = is_array($notification->data) ? $notification->data : [];

        $missingPerson = $this->resolveMissingPerson($data);
        $detection = $this->resolveDetection($data);

        // Fall back to the case attached to the detection.
        if (!$missingPerson && $detection) {
            $missingPerson = MissingPerson::with('photos')->find($detection->missing_person_id);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $notification->id,
                'type' => $notification->type->value,
                'title' => $notification->title,
                'message' => $notification->message,
                'is_unread' => $notification->isUnread(),
                'created_at' => $notification->created_at->toIso8601String(),
                'missing_person' => $missingPerson ? $this->mapPerson($missingPerson) : null,
                'detection' => $detection ? $this->mapDetection($detection) : null,
            ],
        ]);
    }

    /**
     * Resolve the missing person referenced by the notification data.
     */
    private function resolveMissingPerson(array $data): ?MissingPerson
    {
        $id = $data['missing_person_id'] ?? null;
        if ($id) {
            return MissingPerson::with('photos')->find($id);
        }

        $caseNumber = $data['case_number'] ?? null;
        if ($caseNumber) {
            return MissingPerson::with('photos')->where('case_number', $caseNumber)->first();
        }

        return null;
    }

    /**
     * Resolve the detection referenced by the notification data.
     */
    private function resolveDetection(array $data): ?Detection
    {
        $id = $data['detection_id'] ?? null;
        if (!$id) {
            return null;
        }

        return Detection::with('camera')->find($id);
    }

    private function mapPerson(MissingPerson $person): array
    {
        return [
            'id' => $person->id,
            'case_number' => $person->case_number,
            'full_name' => $person->full_name,
            'gender' => $person->gender,
            'date_of_birth' => $person->date_of_birth?->toDateString(),
            'last_seen_location' => $person->last_seen_location,
            'last_seen_latitude' => $person->last_seen_latitude !== null ? (float) $person->last_seen_latitude : null,
            'last_seen_longitude' => $person->last_seen_longitude !== null ? (float) $person->last_seen_longitude : null,
            'last_seen_date' => $person->last_seen_date?->toDateString(),
            'status' => $person->status->value,
            'description' => $person->description,
            'photos' => $person->photos->map(fn ($photo) => [
                'url' => $photo->url(),
                'type' => $photo->type->value,
                'is_primary' => $photo->is_primary,
            ])->values(),
        ];
    }

    private function mapDetection(Detection $detection): array
    {
        $flaskBase = rtrim((string) config('services.python_api.base_url', 'http://127.0.0.1:5000'), '/');

        $capturedImages = [];
        if ($detection->captured_image) {
            foreach (explode(',', $detection->captured_image) as $img) {
                $img = trim($img);
                if ($img === '') {
                    continue;
                }
                if (!str_starts_with($img, 'http')) {
                    $img = $flaskBase . '/outputs/' . ltrim($img, '/');
                }
                $capturedImages[] = $img;
            }
        }

        return [
            'id' => $detection->id,
            'detection_id' => $detection->detection_id,
            'similarity_score' => $detection->similarity_score !== null ? (float) $detection->similarity_score : null,
            'status' => $detection->status->value,
            'detected_at' => $detection->detected_at?->toIso8601String(),
            'captured_images' => $capturedImages,
            'camera' => $detection->camera ? [
                'name' => $detection->camera->name,
                'location' => $detection->camera->location,
                'latitude' => $detection->camera->latitude !== null ? (float) $detection->camera->latitude : null,
                'longitude' => $detection->camera->longitude !== null ? (float) $detection->camera->longitude : null,
            ] : null,
        ];
    }
}
