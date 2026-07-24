<?php

namespace App\Http\Controllers;

use App\Enums\DetectionStatus;
use App\Models\Camera;
use App\Models\Detection;
use Illuminate\Http\Request;

class DetectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Detection::with(['camera', 'reference']);

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('status')) {
            $status = DetectionStatus::tryFrom($request->input('status'));
            if ($status) {
                $query->where('status', $status);
            }
        }

        if ($request->filled('camera_id')) {
            $query->byCamera($request->input('camera_id'));
        }

        $detections = $query->latest('detected_at')->paginate(15)->withQueryString();
        $cameras = Camera::orderBy('name')->get();
        $statuses = DetectionStatus::cases();

        return view('detections.index', compact('detections', 'cameras', 'statuses'));
    }

    public function show(Detection $detection)
    {
        $detection->load(['camera', 'reference', 'reviewedBy', 'alerts', 'verificationRequests']);

        return view('detections.show', compact('detection'));
    }
}
