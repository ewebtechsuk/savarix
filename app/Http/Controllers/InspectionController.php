<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\User;
use App\Notifications\InspectionScheduled;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function index()
    {
        $inspections = Inspection::with('property')->where('agent_id', auth()->id())->get();
        $routePrefix = $this->routePrefix();

        return view('inspections.index', compact('inspections', 'routePrefix'));
    }

    public function create()
    {
        $inspection = new Inspection();
        $routePrefix = $this->routePrefix();

        return view('inspections.edit', compact('inspection', 'routePrefix'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'agent_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date',
            'status' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.status' => 'nullable|string',
            'items.*.photo' => 'nullable|image',
        ]);

        $inspection = Inspection::create($data);

        foreach ($request->input('items', []) as $index => $itemData) {
            $photoPath = null;
            if ($request->hasFile("items.$index.photo")) {
                $photoPath = $request->file("items.$index.photo")->store('inspection_items', 'public');
            }
            $inspection->items()->create([
                'description' => $itemData['description'] ?? '',
                'status' => $itemData['status'] ?? 'pending',
                'photo_path' => $photoPath,
            ]);
        }

        $agent = User::find($inspection->agent_id);
        if ($agent) {
            $agent->notify(new InspectionScheduled($inspection));
        }

        return redirect()->route($this->routePrefix().'.index');
    }

    public function edit(Inspection $inspection)
    {
        $inspection->load('items');
        $routePrefix = $this->routePrefix();

        return view('inspections.edit', compact('inspection', 'routePrefix'));
    }

    public function update(Request $request, Inspection $inspection)
    {
        $data = $request->validate([
            'scheduled_at' => 'required|date',
            'status' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.status' => 'nullable|string',
            'items.*.photo' => 'nullable|image',
        ]);

        $inspection->update($data);
        $inspection->items()->delete();

        foreach ($request->input('items', []) as $index => $itemData) {
            $photoPath = null;
            if ($request->hasFile("items.$index.photo")) {
                $photoPath = $request->file("items.$index.photo")->store('inspection_items', 'public');
            }
            $inspection->items()->create([
                'description' => $itemData['description'] ?? '',
                'status' => $itemData['status'] ?? 'pending',
                'photo_path' => $photoPath,
            ]);
        }

        return redirect()->route($this->routePrefix().'.index');
    }

    private function routePrefix(): string
    {
        return request()->routeIs('agent.*') ? 'agent.inspections' : 'inspections';
    }
}
