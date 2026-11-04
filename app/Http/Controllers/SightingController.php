<?php

namespace App\Http\Controllers;

use App\Enums\SightingStatus;
use App\Models\Sighting;
use App\Models\SightingStatusHistory;
use App\Services\SightingAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SightingController extends Controller
{
    public function index(Request $request)
    {
        \App\Services\Privacy::log('sightings_view', 'sighting');
        $query = Sighting::with(['missingPerson.primaryPhoto', 'evidence']);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();
            $query->where(function ($builder) use ($search) {
                $builder->where('location', 'ilike', "%{$search}%")
                    ->orWhereHas('missingPerson', fn ($person) => $person
                        ->where('full_name', 'ilike', "%{$search}%")
                        ->orWhere('case_number', 'ilike', "%{$search}%"));
            });
        }

        if ($request->filled('status') && SightingStatus::tryFrom($request->input('status'))) {
            $query->where('status', SightingStatus::from($request->input('status')));
        }

        $sightings = $query->latest('sighted_at')->paginate(15)->withQueryString();
        $statuses = SightingStatus::cases();
        $newCount = Sighting::where('status', SightingStatus::New)->count();

        return view('sightings.index', compact('sightings', 'statuses', 'newCount'));
    }

    public function updateStatus(Request $request, Sighting $sighting)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(SightingStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldStatus = $sighting->status;
        $newStatus = SightingStatus::from($validated['status']);
        DB::transaction(function () use ($sighting, $oldStatus, $newStatus, $validated, $request) {
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

        return redirect()->route('sightings.show', $sighting)
            ->with('success', $newStatus === SightingStatus::Confirmed
                ? 'Sighting confirmed and the area alert was generated.'
                : 'Public sighting report status updated.');
    }

    public function show(Sighting $sighting)
    {
        \App\Services\Privacy::log('sighting_view', 'sighting', $sighting->id);
        $sighting->load([
            'missingPerson.primaryPhoto', 'evidence', 'alert',
            'statusHistory.performedBy',
        ]);
        $statuses = SightingStatus::cases();

        return view('sightings.show', compact('sighting', 'statuses'));
    }
}
