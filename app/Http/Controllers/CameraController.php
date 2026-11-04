<?php

namespace App\Http\Controllers;

use App\Enums\CameraStatus;
use App\Models\Camera;
use App\Models\PatrolArea;
use Illuminate\Http\Request;
use App\Services\CameraRecognitionService;

class CameraController extends Controller
{
    public function index(Request $request)
    {
        $query = Camera::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('status')) {
            $status = CameraStatus::tryFrom($request->input('status'));
            if ($status) {
                $query->where('status', $status);
            }
        }

        $cameras = $query->latest()->paginate(15)->withQueryString();

        return view('cameras.index', compact('cameras'));
    }

    public function create()
    {
        $latest = Camera::orderBy('camera_id', 'desc')->first();
        if ($latest) {
            $lastNum = (int) substr($latest->camera_id, 4);
            $cameraId = 'CAM-' . str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $cameraId = 'CAM-001';
        }

        $patrolAreas = PatrolArea::where('is_active', true)->orderBy('name')->get();
        return view('cameras.create', compact('cameraId', 'patrolAreas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'camera_id' => ['required', 'string', 'max:50', 'unique:cameras,camera_id'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'patrol_area_id' => ['required', 'integer', 'exists:patrol_areas,id'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'stream_url' => ['nullable', 'string', 'max:500'],
            'stream_type' => ['nullable', 'string', 'in:mjpeg,snapshot,rtsp,hls,custom,webcam'],
            'username' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', 'string', 'in:online,offline,maintenance'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['registered_by'] = auth()->id();

        Camera::create($validated);

        return redirect()
            ->route('cameras.index')
            ->with('success', 'Camera ' . $validated['camera_id'] . ' registered successfully.');
    }

    public function show(Camera $camera, CameraRecognitionService $recognition)
    {
        $camera->load(['detections' => function ($q) {
            $q->latest()->take(10);
        }, 'registeredBy']);

        $recognitionStatus = null;
        try { $recognitionStatus = $recognition->status($camera); } catch (\Throwable) {}
        return view('cameras.show', compact('camera', 'recognitionStatus'));
    }

    public function edit(Camera $camera)
    {
        $patrolAreas = PatrolArea::where('is_active', true)->orderBy('name')->get();
        return view('cameras.edit', compact('camera', 'patrolAreas'));
    }

    public function update(Request $request, Camera $camera)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'patrol_area_id' => ['required', 'integer', 'exists:patrol_areas,id'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'stream_url' => ['nullable', 'string', 'max:500'],
            'stream_type' => ['nullable', 'string', 'in:mjpeg,snapshot,rtsp,hls,custom,webcam'],
            'username' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', 'string', 'in:online,offline,maintenance'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $camera->update($validated);

        return redirect()
            ->route('cameras.show', $camera)
            ->with('success', 'Camera ' . $camera->camera_id . ' updated successfully.');
    }

    public function destroy(Camera $camera)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $cameraId = $camera->camera_id;
        $camera->delete();

        return redirect()
            ->route('cameras.index')
            ->with('success', 'Camera ' . $cameraId . ' deleted successfully.');
    }

    public function live(Camera $camera, CameraRecognitionService $recognition)
    {
        $streamUrl = $camera->getPythonVideoFeedUrl();
        $recognitionStatus = null;
        try { $recognitionStatus = $recognition->status($camera); } catch (\Throwable) {}

        return view('cameras.live', compact('camera', 'streamUrl', 'recognitionStatus'));
    }

    public function recognition(Request $request, Camera $camera, CameraRecognitionService $recognition)
    {
        $validated = $request->validate(['action' => ['required', 'in:start,stop']]);
        try {
            $result = $recognition->control($camera, $validated['action']);
            return back()->with('success', "Recognition {$result['status']} for {$camera->name}.");
        } catch (\Throwable $error) {
            return back()->with('error', $error->getMessage());
        }
    }
}
