<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Notifications\MaintenanceStatusUpdated;
use Illuminate\Http\Request;

class MaintenanceRequestController extends Controller
{
    /**
     * Show form for tenants to create a maintenance request.
     */
    public function create()
    {
        return view('maintenance.create');
    }

    /**
     * Store a newly created maintenance request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'description' => 'required|string',
        ]);

        if ($tenant = tenant()) {
            $validated['tenant_id'] = $tenant->id;
        }

        $validated['status'] = 'pending';

        MaintenanceRequest::create($validated);

        return redirect()->route('maintenance.create')->with('status', 'Request submitted.');
    }

    /**
     * Display a listing of maintenance requests for admins.
     */
    public function index()
    {
        $requests = MaintenanceRequest::with(['property', 'tenant'])->get();

        return view('maintenance.index', compact('requests'));
    }

    /**
     * Display a single maintenance request.
     */
    public function show(MaintenanceRequest $maintenanceRequest)
    {
        return view('maintenance.show', ['request' => $maintenanceRequest]);
    }

    /**
     * Update the status of a maintenance request.
     */
    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $data = $request->validate([
            'status' => 'required|string',
        ]);

        $maintenanceRequest->update($data);

        if ($maintenanceRequest->tenant) {
            $maintenanceRequest->tenant->notify(new MaintenanceStatusUpdated($maintenanceRequest));
        }

        return redirect()->route('maintenance.show', $maintenanceRequest)->with('status', 'Status updated.');
    }
}

