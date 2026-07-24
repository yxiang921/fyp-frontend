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
        return view('public.report-missing');
    }

    public function store(PublicMissingPersonRequest $request)
    {
        $data = $request->validated();

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
        $data['created_by'] = auth()->id();

        $missingPerson = MissingPerson::create($data);

        // Create timeline entry
        MissingPersonTimeline::create([
            'missing_person_id' => $missingPerson->id,
            'event_type' => 'submitted',
            'description' => 'Case submitted by public user. Awaiting PDRM officer verification.',
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
            ->route('public.case.show', $missingPerson)
            ->with('success', 'Your missing person report has been submitted. A PDRM officer will verify your case shortly.');
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
        // Authorization: only the submitter can view
        if ($missingPerson->created_by !== auth()->id() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'You are not authorized to view this case.');
        }

        $missingPerson->load(['photos', 'timeline.recordedBy']);

        return view('public.case-status', compact('missingPerson'));
    }
}
