<?php

namespace App\Http\Controllers\Api;

use App\Enums\MissingPersonStatus;
use App\Enums\NotificationType;
use App\Enums\PhotoType;
use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Detection;
use App\Models\MissingPerson;
use App\Models\MissingPersonPhoto;
use App\Models\MissingPersonTimeline;
use App\Models\Notification;
use App\Services\PythonApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MissingPersonController extends Controller
{
    /**
     * List missing persons with optional filters.
     *
     * Query params:
     *   - status: active|pending|found|closed (default: all)
     *   - search: search by name, case_number, or location
     *   - per_page: results per page (default: 20, max: 100)
     */
    public function index(Request $request): JsonResponse
    {
        $query = MissingPerson::with('photos');
        $user = auth('sanctum')->user();
        $isStaff = $user && in_array($user->role->value, ['super_admin', 'pdrm_officer', 'patrol_unit'], true);

        // Filter by status
        if ($isStaff) {
            if ($request->filled('status')) {
                $status = MissingPersonStatus::tryFrom($request->input('status'));
                if ($status) {
                    $query->byStatus($status);
                }
            }
        } else {
            // Public users only see active, officer-verified cases.
            $query->byStatus(MissingPersonStatus::Active);
        }

        // Search
        if ($request->filled('search')) {
            if ($isStaff) $query->search($request->input('search'));
            else $query->where(function ($q) use ($request) {
                $search = '%'.$request->input('search').'%';
                $q->where('full_name', 'ilike', $search)->orWhere('case_number', 'ilike', $search)->orWhere('last_seen_location', 'ilike', $search);
            });
        }

        $perPage = max(1, min((int) $request->input('per_page', 20), 100));

        $missingPersons = $query->latest()->paginate($perPage);

        $data = $missingPersons->through(fn (MissingPerson $person) => $this->mapPerson($person));

        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $missingPersons->currentPage(),
                'last_page' => $missingPersons->lastPage(),
                'per_page' => $missingPersons->perPage(),
                'total' => $missingPersons->total(),
                'next_page_url' => $missingPersons->nextPageUrl(),
                'prev_page_url' => $missingPersons->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Store a new missing person report.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'consent' => 'accepted',
            'national_id' => 'nullable|string|max:30',
            'last_seen_latitude' => 'nullable|required_with:last_seen_longitude|numeric|between:-90,90',
            'last_seen_longitude' => 'nullable|required_with:last_seen_latitude|numeric|between:-180,180',
            'full_name'          => 'required|string|max:255',
            'gender'             => 'nullable|string|max:30',
            'date_of_birth'      => 'nullable|date',
            'ethnicity'          => 'nullable|string|max:100',
            'nationality'        => 'nullable|string|max:100',
            'height_cm'          => 'nullable|integer|between:30,250',
            'weight_kg'          => 'nullable|numeric|between:1,500',
            'hair_type'          => 'nullable|string|max:100',
            'hair_color'         => 'nullable|string|max:50',
            'eye_color'          => 'nullable|string|max:50',
            'clothing_description' => 'nullable|string|max:2000',
            'identifying_features' => 'nullable|string|max:2000',
            'description'        => 'nullable|string',
            'last_seen_location' => 'required|string',
            'last_seen_date'     => 'nullable|date',
            'photo'              => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $duplicateReview = app(\App\Services\DuplicateCaseService::class)->guard($request, $validated);

        // Generate a unique case number: MP-YYMMDD-XXXX
        $caseNumber = 'MP-'
            . now()->format('ymd') . '-'
            . strtoupper(Str::random(4));

        // Ensure uniqueness
        while (MissingPerson::where('case_number', $caseNumber)->exists()) {
            $caseNumber = 'MP-'
                . now()->format('ymd') . '-'
                . strtoupper(Str::random(4));
        }

        $missingPerson = MissingPerson::create([
            'national_id' => $validated['national_id'] ?? null,
            'last_seen_latitude' => $validated['last_seen_latitude'] ?? null,
            'last_seen_longitude' => $validated['last_seen_longitude'] ?? null,
            'consented_at' => now(),
            'duplicate_review' => $duplicateReview,
            'created_by' => auth('sanctum')->id(),
            'case_number'        => $caseNumber,
            'full_name'          => $validated['full_name'],
            'gender'             => $validated['gender'] ?? 'unknown',
            'date_of_birth'      => $validated['date_of_birth'] ?? null,
            'ethnicity'          => $validated['ethnicity'] ?? null,
            'nationality'        => $validated['nationality'] ?? null,
            'height_cm'          => $validated['height_cm'] ?? null,
            'weight_kg'          => $validated['weight_kg'] ?? null,
            'hair_type'          => $validated['hair_type'] ?? null,
            'hair_color'         => $validated['hair_color'] ?? null,
            'eye_color'          => $validated['eye_color'] ?? null,
            'clothing_description' => $validated['clothing_description'] ?? null,
            'identifying_features' => $validated['identifying_features'] ?? null,
            'description'        => $validated['description'] ?? null,
            'last_seen_location' => $validated['last_seen_location'],
            'last_seen_date'     => $validated['last_seen_date'] ?? null,
            'status'             => MissingPersonStatus::Pending,
        ]);

        // Handle photo upload (Cloudinary first, local storage as fallback)
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $path = null;

            try {
                $result = cloudinary()->uploadApi()->upload($photo->getRealPath(), [
                    'folder' => 'findsight/missing-persons/' . $missingPerson->id,
                    'transformation' => [
                        'width' => 800,
                        'height' => 800,
                        'crop' => 'limit',
                    ],
                ]);
                $path = $result['secure_url'];
            } catch (\Throwable $e) {
                $path = $photo->store('missing-persons', 'public');
            }

            MissingPersonPhoto::create([
                'missing_person_id' => $missingPerson->id,
                'path'              => $path,
                'type'              => PhotoType::Front,
                'is_primary'        => true,
                'uploaded_by'       => $request->user()?->id,
            ]);
        }

        // Keep mobile-created cases compatible with the web workflow. The
        // Python service safely logs failures and can be retried later.
        $missingPerson->load('photos');
        if ($missingPerson->photos->isNotEmpty()) {
            PythonApiService::syncMissingPerson($missingPerson);
        }

        return response()->json([
            'success' => true,
            'message' => 'Missing person report submitted.',
            'data'    => ['case_number' => $missingPerson->case_number, 'status' => $missingPerson->status->value, 'face_check' => $duplicateReview['face_status']],
        ], 201);
    }

    /**
     * Show a single missing person with full details.
     */
    public function show(Request $request, MissingPerson $missingPerson): JsonResponse
    {
        $user = auth('sanctum')->user();

        $isStaff = $user && in_array($user->role->value, ['super_admin', 'pdrm_officer', 'patrol_unit'], true);

        if (! $isStaff && $missingPerson->status !== MissingPersonStatus::Active) {
            abort(404);
        }
        $missingPerson->load('photos');

        if ($isStaff) {
            \App\Services\Privacy::log('case_view', 'case', $missingPerson->id);
            $missingPerson->load(['detections' => fn ($query) => $query->with('camera')->latest('detected_at'),
                'alerts' => fn ($query) => $query->with('camera')->latest()]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->mapPerson($missingPerson, $missingPerson->relationLoaded('detections')),
        ]);
    }

    /**
     * Update a missing person case status (staff only).
     */
    public function updateStatus(Request $request, MissingPerson $missingPerson): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,active,found,closed,reopened'],
        ]);

        $oldStatus = $missingPerson->status->value;
        $newStatus = MissingPersonStatus::from($validated['status']);

        $missingPerson->update(['status' => $newStatus]);

        if ($newStatus === MissingPersonStatus::Active) {
            $missingPerson->load('photos');
            if ($missingPerson->photos->isNotEmpty()) {
                PythonApiService::syncMissingPerson($missingPerson);
            }
        }

        MissingPersonTimeline::create([
            'missing_person_id' => $missingPerson->id,
            'event_type' => 'status_change',
            'description' => "Status changed from {$oldStatus} to {$newStatus->value} by {$request->user()->name}.",
            'recorded_by' => $request->user()->id,
        ]);

        // Notify the case creator (if any) about the status change.
        if ($missingPerson->created_by) {
            Notification::create([
                'user_id' => $missingPerson->created_by,
                'type' => NotificationType::CaseUpdate,
                'title' => 'Case status updated',
                'message' => "Case {$missingPerson->case_number} ({$missingPerson->full_name}) is now '{$newStatus->label()}'.",
                'data' => [
                    'missing_person_id' => $missingPerson->id,
                    'status' => $newStatus->value,
                ],
            ]);
        }

        $missingPerson->load('photos');

        return response()->json([
            'success' => true,
            'message' => 'Case status updated.',
            'data' => [
                'id' => $missingPerson->id,
                'case_number' => $missingPerson->case_number,
                'full_name' => $missingPerson->full_name,
                'gender' => $missingPerson->gender,
                'status' => $missingPerson->status->value,
                'description' => $missingPerson->description,
                'last_seen_location' => $missingPerson->last_seen_location,
                'last_seen_date' => $missingPerson->last_seen_date?->toDateString(),
                'photos' => $missingPerson->photos->map(fn ($photo) => [
                    'url' => $photo->url(),
                    'type' => $photo->type->value,
                    'is_primary' => $photo->is_primary,
                ])->values(),
                'updated_at' => $missingPerson->updated_at->toIso8601String(),
            ],
        ]);
    }

    private function mapPerson(MissingPerson $person, bool $includeActivity = false): array
    {
        $staff = in_array(auth('sanctum')->user()?->role->value, ['super_admin', 'pdrm_officer', 'patrol_unit'], true);
        $data = [
            'id' => $person->id,
            'case_number' => $person->case_number,
            'full_name' => $person->full_name,
            'gender' => $person->gender,
            'date_of_birth' => $person->date_of_birth?->toDateString(),
            'ethnicity' => $person->ethnicity,
            'nationality' => $person->nationality,
            'height_cm' => $person->height_cm,
            'weight_kg' => $person->weight_kg !== null ? (float) $person->weight_kg : null,
            'hair_type' => $person->hair_type,
            'hair_color' => $person->hair_color,
            'eye_color' => $person->eye_color,
            'clothing_description' => $person->clothing_description,
            'identifying_features' => $person->identifying_features,
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
            'created_at' => $person->created_at->toIso8601String(),
            'updated_at' => $person->updated_at->toIso8601String(),
        ];

        if ($includeActivity) {
            $data['detections'] = $person->detections->map(fn (Detection $detection) => $this->mapDetection($detection))->values();
            $data['alerts'] = $person->alerts->map(fn (Alert $alert) => $this->mapAlert($alert))->values();
        }

        if (!$staff) {
            $data['date_of_birth'] = null;
            $data['last_seen_latitude'] = null;
            $data['last_seen_longitude'] = null;
            foreach (['full_name', 'description', 'clothing_description', 'identifying_features', 'last_seen_location'] as $field) {
                $data[$field] = \App\Services\Privacy::redact($data[$field]);
            }
        }
        return $data;
    }

    private function mapDetection(Detection $detection): array
    {
        $flaskBase = rtrim((string) config('services.python_api.base_url', 'http://127.0.0.1:5000'), '/');
        $images = collect(explode(',', (string) $detection->captured_image))
            ->map(fn (string $image) => trim($image))
            ->filter()
            ->map(fn (string $image) => str_starts_with($image, 'http') ? $image : $flaskBase . '/outputs/' . ltrim($image, '/'))
            ->values();

        return [
            'id' => $detection->id,
            'detection_id' => $detection->detection_id,
            'similarity_score' => $detection->similarity_score !== null ? (float) $detection->similarity_score : null,
            'status' => $detection->status->value,
            'detected_at' => $detection->detected_at?->toIso8601String(),
            'captured_images' => $images,
            'camera' => $this->mapCamera($detection->camera),
        ];
    }

    private function mapAlert(Alert $alert): array
    {
        return [
            'id' => $alert->id,
            'alert_id' => $alert->alert_id,
            'detection_id' => $alert->detection_id,
            'similarity_score' => $alert->similarity_score !== null ? (float) $alert->similarity_score : null,
            'status' => $alert->status->value,
            'description' => $alert->description,
            'resolved_at' => $alert->resolved_at?->toIso8601String(),
            'created_at' => $alert->created_at->toIso8601String(),
            'camera' => $this->mapCamera($alert->camera),
        ];
    }

    private function mapCamera($camera): ?array
    {
        return $camera ? [
            'name' => $camera->name,
            'location' => $camera->location,
            'latitude' => $camera->latitude !== null ? (float) $camera->latitude : null,
            'longitude' => $camera->longitude !== null ? (float) $camera->longitude : null,
        ] : null;
    }
}
