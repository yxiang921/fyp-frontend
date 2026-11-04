<?php

namespace App\Http\Controllers;

use App\Models\MissingPerson;
use App\Models\SightingEvidence;
use App\Services\Privacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportingController extends Controller
{
    public function track(Request $request)
    {
        $data = $request->validate(['case_number' => 'required|string|max:40']);
        $case = MissingPerson::whereRaw('UPPER(case_number) = ?', [strtoupper(trim($data['case_number']))])->first();
        if (!$case) throw \Illuminate\Validation\ValidationException::withMessages(['case_number' => __('Case not found.')]);
        Privacy::log('guest_status_view', 'case', $case->id);
        $status = ['case_number' => $case->case_number, 'status' => $case->status->value,
            'submitted_at' => $case->created_at->toDateString(), 'updated_at' => $case->updated_at->toDateString()];
        return $request->expectsJson() ? response()->json(['data' => $status])->header('Cache-Control', 'no-store')
            : response()->view('public.track', ['result' => $status])->header('Cache-Control', 'no-store');
    }

    public function evidence(SightingEvidence $evidence)
    {
        abort_if($evidence->purged_at || $evidence->expires_at->isPast(), 410);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($evidence->path), 404);
        if (!hash_equals($evidence->sha256, hash_file('sha256', $disk->path($evidence->path)))) {
            Privacy::log('integrity_failure', 'evidence', $evidence->id);
            abort(409, 'Evidence integrity check failed.');
        }
        Privacy::log('download', 'evidence', $evidence->id);
        return $disk->download($evidence->path, 'evidence-'.$evidence->id.'.'.pathinfo($evidence->path, PATHINFO_EXTENSION), [
            'Content-Type' => $evidence->mime_type, 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function map(MissingPerson $missingPerson)
    {
        Privacy::log('map_view', 'case', $missingPerson->id);
        return response()->json(['data' => app(\App\Services\CaseMapService::class)->points($missingPerson)])->header('Cache-Control', 'private, no-store');
    }
}
