<?php

namespace App\Http\Controllers;

use App\Enums\MissingPersonStatus;
use App\Models\MissingPerson;
use App\Models\MissingPersonTimeline;
use App\Http\Requests\PublicMissingPersonRequest;
use App\Services\PythonApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicSubmissionController extends Controller
{
    public function create()
    {
        return view('public.report-form');
    }

    public function store(PublicMissingPersonRequest $request)
    {
        $data = $request->validated();
        $data['duplicate_review'] = app(\App\Services\DuplicateCaseService::class)->guard($request, $data);
        $data['consented_at'] = now();

        // Remove honeypot field from data
        unset($data['website'], $data['consent'], $data['duplicate_acknowledged']);

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

        $data['case_number'] = $caseNumber;
        $data['status'] = MissingPersonStatus::Pending->value;
        $data['created_by'] = auth()->check() ? auth()->id() : null;

        $missingPerson = MissingPerson::create($data);

        // Create timeline entry
        MissingPersonTimeline::create([
            'missing_person_id' => $missingPerson->id,
            'event_type' => 'submitted',
            'description' => auth()->check()
                ? 'Case submitted by public user. Awaiting PDRM officer verification.'
                : 'Case submitted by anonymous public user via online form. Awaiting PDRM officer verification.',
            'recorded_by' => auth()->check() ? auth()->id() : null,
        ]);

        // Handle photo upload to Cloudinary
        if ($request->hasFile('photo')) {
            $result = cloudinary()->uploadApi()->upload($request->file('photo')->getRealPath(), [
                'folder' => 'findsight/missing-persons/' . $missingPerson->id,
                'transformation' => [
                    'width' => 800,
                    'height' => 800,
                    'crop' => 'limit',
                ],
            ]);
            $missingPerson->photos()->create([
                'path' => $result['secure_url'],
                'type' => 'front',
                'is_primary' => true,
                'uploaded_by' => auth()->check() ? auth()->id() : null,
            ]);
        }

        // Sync to Python API server (fire-and-forget)
        PythonApiService::syncMissingPerson($missingPerson);

        // Redirect based on auth status
        if (auth()->check()) {
            return redirect()
                ->route('public.case.show', $missingPerson)
                ->with('success', 'Your missing person report has been submitted. A PDRM officer will verify your case shortly.');
        }

        return redirect()
            ->route('public.submitted', $missingPerson->case_number)
            ->with('submitted_case_number', $missingPerson->case_number);
    }

    public function submitted(string $caseNumber)
    {
        $exists = MissingPerson::where('case_number', $caseNumber)->exists();
        if (!$exists) {
            abort(404);
        }

        return view('public.report-submitted', [
            'caseNumber' => session('submitted_case_number', $caseNumber),
        ]);
    }

    public function myCases()
    {
        $myCases = MissingPerson::where('created_by', auth()->id())
            ->latest()
            ->paginate(10);

        return view('public.my-cases', compact('myCases'));
    }

    public function showCase(MissingPerson $missingPerson)
    {
        // Authorization: only the submitter or super admin can view
        if ($missingPerson->created_by !== auth()->id() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'You are not authorized to view this case.');
        }

        $missingPerson->load(['photos', 'timeline.recordedBy']);

        return view('public.case-status', compact('missingPerson'));
    }
}
