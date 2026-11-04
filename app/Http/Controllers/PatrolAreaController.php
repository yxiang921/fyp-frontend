<?php

namespace App\Http\Controllers;

use App\Models\PatrolArea;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatrolAreaController extends Controller
{
    public function index()
    {
        $areas = PatrolArea::withCount(['users', 'cameras'])->orderBy('name')->get();
        return view('patrol-areas.index', compact('areas'));
    }

    public function create()
    {
        return view('patrol-areas.form', ['area' => new PatrolArea]);
    }

    public function store(Request $request)
    {
        PatrolArea::create($this->validated($request));
        return redirect()->route('patrol-areas.index')->with('success', 'Patrol area created.');
    }

    public function edit(PatrolArea $patrolArea)
    {
        return view('patrol-areas.form', ['area' => $patrolArea]);
    }

    public function update(Request $request, PatrolArea $patrolArea)
    {
        $patrolArea->update($this->validated($request, $patrolArea));
        return redirect()->route('patrol-areas.index')->with('success', 'Patrol area updated.');
    }

    public function destroy(PatrolArea $patrolArea)
    {
        if ($patrolArea->users()->exists() || $patrolArea->cameras()->exists()) {
            return back()->with('error', 'Reassign patrol units and cameras before deleting this area.');
        }
        $patrolArea->delete();
        return back()->with('success', 'Patrol area deleted.');
    }

    private function validated(Request $request, ?PatrolArea $area = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('patrol_areas')->ignore($area)],
            'code' => ['required', 'alpha_dash', 'max:30', Rule::unique('patrol_areas')->ignore($area)],
            'description' => ['nullable', 'string', 'max:1000'],
            'center_latitude' => ['nullable', 'required_with:center_longitude', 'numeric', 'between:-90,90'],
            'center_longitude' => ['nullable', 'required_with:center_latitude', 'numeric', 'between:-180,180'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }
}
