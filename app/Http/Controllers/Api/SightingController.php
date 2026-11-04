<?php

namespace App\Http\Controllers\Api;

use App\Enums\NotificationType;
use App\Enums\MissingPersonStatus;
use App\Enums\SightingStatus;
use App\Http\Controllers\Controller;
use App\Models\MissingPerson;
use App\Models\MissingPersonTimeline;
use App\Models\Notification;
use App\Models\Sighting;
use App\Models\SightingStatusHistory;
use App\Services\SightingAlertService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SightingController extends Controller
{
    /**
     * Submit a public sighting report for a missing person.
     */
    public function store(Request $request, MissingPerson $missingPerson): JsonResponse
    {
        if ($missingPerson->status !== MissingPersonStatus::Active) {
            abort(404);
        }

        $validated = $request->validate([
            'consent' => 'accepted',
            'evidence' => 'nullable|array|max:3',
            'evidence.*' => 'file|mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm|max:20480',
            'reporter_name' => 'nullable|string|max:255',
            'reporter_phone' => 'nullable|string|max:20',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|required_with:longitude|numeric|between:-90,90',
            'longitude' => 'nullable|required_with:latitude|numeric|between:-180,180',
            'sighted_at' => 'required|date|before_or_equal:now',
            'notes' => 'nullable|string|max:5000',
        ]);

        $paths = [];
        try {
        $sighting = \Illuminate\Support\Facades\DB::transaction(function () use ($missingPerson, $validated, $request, &$paths) {
        $sighting = $missingPerson->sightings()->create([
            'consented_at' => now(),
            'reporter_name' => $validated['reporter_name'] ?? null,
            'reporter_phone' => $validated['reporter_phone'] ?? null,
            'location' => $validated['location'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'sighted_at' => $validated['sighted_at'],
            'notes' => $validated['notes'] ?? null,
            'status' => SightingStatus::New,
        ]);

        foreach ($request->file('evidence', []) as $file) {
            $path = $file->store('sighting-evidence', 'local');
            if (!$path) throw new \RuntimeException('Evidence could not be stored.');
            $paths[] = $path;
            $sighting->evidence()->create([
                'path' => $path, 'mime_type' => $file->getMimeType(), 'size' => $file->getSize(),
                'sha256' => hash_file('sha256', $file->getRealPath()),
                'expires_at' => now()->addDays(config('reporting.evidence_retention_days')),
            ]);
        }
        return $sighting;
        });
        } catch (\Throwable $error) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($paths);
            throw $error;
        }

        MissingPersonTimeline::create([
            'missing_person_id' => $missingPerson->id,
            'event_type' => 'sighting',
            'description' => 'Public sighting reported at ' . $validated['location'] . '.',
            'recorded_by' => $request->user()?->id,
        ]);

        \Illuminate\Support\Facades\DB::afterCommit(fn () => $this->notifyStaff($missingPerson, $sighting));

        return response()->json([
            'success' => true,
            'message' => 'Sighting report submitted. Thank you.',
            'data' => ['id' => $sighting->id, 'status' => $sighting->status->value],
        ], 201);
    }

    /**
     * List sightings (staff only) with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        \App\Services\Privacy::log('sightings_view', 'sighting');
        $query = Sighting::with('missingPerson')->latest();

        if ($request->filled('missing_person_id')) {
            $query->where('missing_person_id', $request->integer('missing_person_id'));
        }

        if ($request->filled('status') && SightingStatus::tryFrom($request->status)) {
            $query->where('status', SightingStatus::from($request->status));
        }

        $sightings = $query->paginate(max(1, min(100, (int) $request->input('per_page', 20))));

        return response()->json([
            'success' => true,
            'data' => $sightings->through(fn (Sighting $s) => $this->map($s))->items(),
            'pagination' => [
                'current_page' => $sightings->currentPage(),
                'last_page' => $sightings->lastPage(),
                'per_page' => $sightings->perPage(),
                'total' => $sightings->total(),
            ],
        ]);
    }

    /**
     * Update a sighting's review status (staff only).
     */
    public function updateStatus(Request $request, Sighting $sighting): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,reviewed,confirmed,dismissed'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldStatus = $sighting->status;
        $newStatus = SightingStatus::from($validated['status']);
        \Illuminate\Support\Facades\DB::transaction(function () use ($sighting, $oldStatus, $newStatus, $validated, $request) {
            $sighting->update(['status' => $newStatus]);
            SightingStatusHistory::create([
                'sighting_id' => $sighting->id,
                'from_status' => $oldStatus->value,
                'to_status' => $newStatus->value,
                'notes' => $validated['notes'] ?? null,
                'performed_by' => $request->user()->id,
            ]);
            if ($newStatus === SightingStatus::Confirmed) {
                app(SightingAlertService::class)->createForConfirmedSighting($sighting, $request->user()->id);
            }
        });

        return response()->json([
            'success' => true,
            'data' => $this->map($sighting),
        ]);
    }

    /**
     * Notify super admins and PDRM officers about a new sighting.
     */
    private function notifyStaff(MissingPerson $missingPerson, Sighting $sighting): void
    {
        $staff = User::whereIn('role', ['super_admin', 'pdrm_officer'])
            ->where('is_active', true)
            ->get();

        foreach ($staff as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => NotificationType::CaseUpdate,
                'title' => 'New sighting reported',
                'message' => 'Sighting of ' . $missingPerson->full_name . ' reported at ' . $sighting->location . '.',
                'data' => [
                    'sighting_id' => $sighting->id,
                    'missing_person_id' => $missingPerson->id,
                ],
            ]);
        }
    }

    private function map(Sighting $sighting): array
    {
        return [
            'id' => $sighting->id,
            'missing_person_id' => $sighting->missing_person_id,
            'missing_person_name' => $sighting->missingPerson?->full_name,
            'case_number' => $sighting->missingPerson?->case_number,
            'reporter_name' => $sighting->reporter_name,
            'reporter_phone' => $sighting->reporter_phone,
            'location' => $sighting->location,
            'latitude' => $sighting->latitude !== null ? (float) $sighting->latitude : null,
            'longitude' => $sighting->longitude !== null ? (float) $sighting->longitude : null,
            'sighted_at' => $sighting->sighted_at?->toIso8601String(),
            'notes' => $sighting->notes,
            'status' => $sighting->status->value,
            'created_at' => $sighting->created_at->toIso8601String(),
        ];
    }
}
