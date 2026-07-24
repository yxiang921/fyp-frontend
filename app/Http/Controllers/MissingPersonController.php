<?php

namespace App\Http\Controllers;

use App\Enums\MissingPersonStatus;
use App\Models\MissingPerson;
use App\Models\MissingPersonTimeline;
use App\Http\Requests\StoreMissingPersonRequest;
use App\Http\Requests\UpdateMissingPersonRequest;
use App\Services\PythonApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MissingPersonController extends Controller
{
    public function index(Request $request)
    {
        $query = MissingPerson::with('primaryPhoto', 'creator');

        // Search
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = MissingPersonStatus::tryFrom($request->input('status'));
            if ($status) {
                $query->byStatus($status);
            }
        }

        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }

        $missingPersons = $query->latest()->paginate(15)->withQueryString();

        return view('missing-persons.index', compact('missingPersons'));
    }

    public function create()
    {
        // Auto-generate case number
        $latest = MissingPerson::where('case_number', 'like', 'MP-' . date('Y') . '-%')
            ->orderBy('case_number', 'desc')
            ->first();

        if ($latest) {
            $lastNum = (int) substr($latest->case_number, -4);
            $caseNumber = 'MP-' . date('Y') . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $caseNumber = 'MP-' . date('Y') . '-0001';
        }

        return view('missing-persons.create', compact('caseNumber'));
    }

    public function store(StoreMissingPersonRequest $request)
    {
        $data = $request->validated();

        // Auto-generate case number if not provided
        if (empty($data['case_number'])) {
            $latest = MissingPerson::where('case_number', 'like', 'MP-' . date('Y') . '-%')
                ->orderBy('case_number', 'desc')
                ->first();
            if ($latest) {
                $lastNum = (int) substr($latest->case_number, -4);
                $data['case_number'] = 'MP-' . date('Y') . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $data['case_number'] = 'MP-' . date('Y') . '-0001';
            }
        }

        // Officer-created cases are auto-verified (Active)
        // Public submissions come through PublicSubmissionController with Pending status
        $data['status'] = MissingPersonStatus::Active->value;
        $data['created_by'] = auth()->id();

        $missingPerson = MissingPerson::create($data);

        // Create timeline entry
        MissingPersonTimeline::create([
            'missing_person_id' => $missingPerson->id,
            'event_type' => 'case_created',
            'description' => 'Case created by officer. Facial recognition monitoring active.',
            'recorded_by' => auth()->id(),
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('missing-persons/' . $missingPerson->id, 'public');
            $missingPerson->photos()->create([
                'path' => $path,
                'type' => 'front',
                'is_primary' => true,
                'uploaded_by' => auth()->id(),
            ]);
        }

        // Sync to Python API server (fire-and-forget)
        PythonApiService::syncMissingPerson($missingPerson);

        return redirect()
            ->route('missing-persons.show', $missingPerson)
            ->with('success', 'Missing person case ' . $data['case_number'] . ' created successfully.');
    }

    public function show(MissingPerson $missingPerson)
    {
        $missingPerson->load(['creator', 'photos', 'timeline.recordedBy', 'detections.camera', 'alerts']);

        $detectionStats = [
            'total' => $missingPerson->detections()->count(),
            'highConfidence' => $missingPerson->detections()->where('similarity_score', '>=', 80)->count(),
            'alertsGenerated' => $missingPerson->alerts()->count(),
            'pendingVerification' => $missingPerson->detections()->where('status', 'new')->count(),
        ];

        $detectedCameras = $missingPerson->detections()
            ->with('camera')
            ->get()
            ->pluck('camera.name')
            ->unique()
            ->filter()
            ->values();

        return view('missing-persons.show', compact('missingPerson', 'detectionStats', 'detectedCameras'));
    }

    public function edit(MissingPerson $missingPerson)
    {
        return view('missing-persons.edit', compact('missingPerson'));
    }

    public function update(UpdateMissingPersonRequest $request, MissingPerson $missingPerson)
    {
        $data = $request->validated();

        $oldStatus = $missingPerson->status;
        $missingPerson->update($data);

        // If status changed, create timeline entry
        if (isset($data['status']) && $data['status'] !== $oldStatus->value) {
            $newStatus = MissingPersonStatus::from($data['status']);
            MissingPersonTimeline::create([
                'missing_person_id' => $missingPerson->id,
                'event_type' => 'status_changed',
                'description' => 'Status changed from ' . $oldStatus->label() . ' to ' . $newStatus->label() . '.',
                'recorded_by' => auth()->id(),
            ]);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('missing-persons/' . $missingPerson->id, 'public');
            // Set existing photos to non-primary
            $missingPerson->photos()->update(['is_primary' => false]);
            $missingPerson->photos()->create([
                'path' => $path,
                'type' => 'front',
                'is_primary' => true,
                'uploaded_by' => auth()->id(),
            ]);
        }

        return redirect()
            ->route('missing-persons.show', $missingPerson)
            ->with('success', 'Case ' . $missingPerson->case_number . ' updated successfully.');
    }

    public function destroy(MissingPerson $missingPerson)
    {
        // Only SuperAdmin can delete
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $caseNumber = $missingPerson->case_number;

        // Delete photos from storage
        foreach ($missingPerson->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }

        $missingPerson->delete();

        return redirect()
            ->route('missing-persons.index')
            ->with('success', 'Case ' . $caseNumber . ' deleted successfully.');
    }

    public function verify(MissingPerson $missingPerson)
    {
        // Only PDRM Officers and SuperAdmin can verify
        if (!in_array(auth()->user()->role->value, ['super_admin', 'pdrm_officer'])) {
            abort(403, 'Unauthorized action.');
        }

        if ($missingPerson->status !== MissingPersonStatus::Pending) {
            return back()->with('error', 'This case is not pending verification.');
        }

        $missingPerson->update(['status' => MissingPersonStatus::Active]);

        MissingPersonTimeline::create([
            'missing_person_id' => $missingPerson->id,
            'event_type' => 'verified',
            'description' => 'Case verified and activated by ' . auth()->user()->name . '. Facial recognition monitoring enabled.',
            'recorded_by' => auth()->id(),
        ]);

        return redirect()
            ->route('missing-persons.show', $missingPerson)
            ->with('success', 'Case ' . $missingPerson->case_number . ' verified and activated. Facial recognition monitoring is now enabled.');
    }
}
