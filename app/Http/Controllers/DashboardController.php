<?php

namespace App\Http\Controllers;

use App\Models\ContactViewing;
use App\Models\Offer;
use App\Models\Property;
use App\Models\SavarixTenancy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $today = now()->startOfDay();

        $viewings = ContactViewing::with(['property', 'contact'])
            ->whereDate('date', $today)
            ->orderBy('date')
            ->get();

        $metrics = [
            'active_listings' => Property::where('status', 'available')->count(),
            'upcoming_viewings' => $viewings->count(),
            'pending_offers' => Offer::where('status', 'pending')->count(),
            'active_tenancies' => SavarixTenancy::where('status', 'active')->count(),
        ];

        $quickActions = [
            ['label' => 'Add new listing', 'description' => 'Upload photos, set price and publish.', 'href' => route('properties.create')],
            ['label' => 'Schedule viewing', 'description' => 'Find the slot, notify the client.', 'href' => route('contacts.index')],
            ['label' => 'Record offer', 'description' => 'Capture amount and conditions.', 'href' => route('properties.index')],
            ['label' => 'Send update email', 'description' => 'Automate status updates for vendors.', 'href' => route('contacts.index')],
        ];

        return view('dashboard', compact('user', 'viewings', 'metrics', 'quickActions'));
    }
}
