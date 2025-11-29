<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\PropertyFeature;
use App\Models\PropertyMedia;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Facades\Tenancy;
use Illuminate\Support\Facades\Http;
use App\Models\MarketingEvent;
use App\Models\ContactViewing;
use App\Models\Offer;
use App\Services\ApplicantMatcher;

class PropertyController extends Controller
{
    public function __construct(private ApplicantMatcher $applicantMatcher)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenant = tenant();
        if (!$tenant) {
            abort(404, 'Tenant not found.');
        }
        $company_id = $tenant->company_id;
        $query = Property::where('company_id', $company_id);
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('city', 'like', "%$search%")
                  ->orWhere('type', 'like', "%$search%")
                  ->orWhere('status', 'like', "%$search%") ;
            });
        }

        if ($request->filled('origin') && $request->filled('radius')) {
            $coords = $this->geocodeAddress($request->input('origin'));
            if ($coords) {
                $lat = $coords['lat'];
                $lng = $coords['lng'];
                $radius = $request->input('radius');
                $query->selectRaw("properties.*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])
                      ->having('distance', '<=', $radius)
                      ->orderBy('distance');
            }
        }

        $properties = $query->get();
        // Always return the view, even if $properties is empty
        return view('properties.index', compact('properties'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $landlord_id = $request->get('landlord_id');
        // Static features list (replace with DB if you add a master table)
        $featuresList = [
            'Fully Furnished', 'River view', 'Shops and amenities nearby', 'Air Conditioning',
            'Gym', 'Guest cloakroom', 'Mezzanine', 'Fitted Kitchen', 'Communal Garden',
            'Roof Terrace', 'Balcony', 'Underground Parking', 'Driveway', 'Parking',
            'En suite', 'Video Entry', 'Double glazing', 'Conservatory', 'Concierge',
            'Close to public transport', 'Un-Furnished', 'Swimming Pool', '24 hour on-site security',
            'Receptionist', 'Meeting Room and Conference Facilities'
        ];
        return view('properties.create', compact('landlord_id', 'featuresList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'postcode' => 'nullable|string',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'media.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'publish_to_portal' => 'boolean',
            'send_marketing_campaign' => 'boolean',
            'marketing_notes' => 'nullable|string',
            'featured_media' => 'nullable|integer|exists:property_media,id',
            'media_order' => 'nullable|array',
            'media_order.*' => 'integer',
        ]);
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('properties', 'public');
        }
        if (auth()->user() && auth()->user()->is_admin) {
            $validated['vendor_id'] = $request->input('vendor_id');
            $validated['landlord_id'] = $request->input('landlord_id');
            $validated['applicant_id'] = $request->input('applicant_id');
            $validated['notes'] = $request->input('notes');
            if ($request->hasFile('document')) {
                $validated['document'] = $request->file('document')->store('property_docs', 'public');
            }
        }
        $validated['publish_to_portal'] = $request->boolean('publish_to_portal');
        $validated['send_marketing_campaign'] = $request->boolean('send_marketing_campaign');
        $marketingNotes = trim($validated['marketing_notes'] ?? '');
        unset($validated['marketing_notes']);
        $tenant = tenant(); // Stancl Tenancy v3+ helper
        if ($tenant) {
            $validated['tenant_id'] = $tenant->id;
        }
        $coords = null;
        if (!empty($validated['address']) || !empty($validated['city']) || !empty($validated['postcode'])) {
            $coords = $this->geocodeAddress(trim(($validated['address'] ?? '') . ' ' . ($validated['city'] ?? '') . ' ' . ($validated['postcode'] ?? '')));
            if ($coords) {
                $validated['latitude'] = $coords['lat'];
                $validated['longitude'] = $coords['lng'];
            }
        }
        if ($marketingNotes !== '') {
            $validated['activity_log'] = [
                'marketing_notes' => [[
                    'note' => $marketingNotes,
                    'recorded_at' => now()->toIso8601String(),
                    'author_id' => auth()->id(),
                ]],
            ];
        }

        $property = Property::create($validated);

        $featuredMediaCreated = false;
        if ($request->hasFile('media')) {
            $order = $property->media()->max('order') ?? 0;
            foreach ($request->file('media') as $file) {
                $order++;
                $path = Storage::disk('public')->putFile('property_media', $file);
                $media = $property->media()->create([
                    'file_path' => $path,
                    'type' => $file->getClientMimeType(),
                    'order' => $order,
                ]);

                if (! $featuredMediaCreated) {
                    $media->update(['is_featured' => true]);
                    $featuredMediaCreated = true;
                }
            }
        }

        // Save features
        $features = $request->input('features', []);
        foreach ($features as $feature) {
            PropertyFeature::create([
                'property_id' => $property->id,
                'name' => $feature,
                'value' => 1
            ]);
        }
        return redirect()->route('properties.index')->with('success', 'Property created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property)
    {
        $tenant = tenant(); // Stancl Tenancy v3+ helper
        if (!$tenant || $property->tenant_id !== $tenant->id) {
            abort(404, 'Property not found for this tenant.');
        }
        $property->load(['media', 'features', 'landlord', 'documents']);
        $features = $property->features()->pluck('name')->toArray();

        $marketingEvents = MarketingEvent::query()
            ->where('metadata->property_id', $property->id)
            ->orderByDesc('occurred_at')
            ->limit(5)
            ->get();

        $marketingStats = [
            'media_count' => $property->media->count(),
            'document_count' => $property->documents->count(),
            'feature_count' => count($features),
            'portal_status' => $property->publish_to_portal ? 'Live' : 'Offline',
            'campaign_status' => $property->send_marketing_campaign ? 'Enabled' : 'Disabled',
            'last_updated' => optional($property->updated_at)->diffForHumans(),
        ];

        $completed = 0;
        $checklistTotal = 3;
        if ($marketingStats['media_count'] > 0) {
            $completed++;
        }
        if ($marketingStats['document_count'] > 0) {
            $completed++;
        }
        if ($property->publish_to_portal || $property->send_marketing_campaign) {
            $completed++;
        }
        $marketingStats['readiness'] = $checklistTotal > 0 ? (int) round(($completed / $checklistTotal) * 100) : 0;

        $viewings = ContactViewing::with('contact')
            ->where('property_id', $property->id)
            ->orderBy('date')
            ->get();

        $offers = $property->offers()->with('contact')->orderByDesc('offered_at')->get();
        $tenancies = $property->tenancies()->with('contact')->orderByDesc('start_date')->get();
        $matches = $this->applicantMatcher->match($property);

        return view('properties.show', compact('property', 'features', 'marketingEvents', 'marketingStats', 'viewings', 'offers', 'tenancies', 'matches'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Property $property)
    {
        $featuresList = [
            'Fully Furnished', 'River view', 'Shops and amenities nearby', 'Air Conditioning',
            'Gym', 'Guest cloakroom', 'Mezzanine', 'Fitted Kitchen', 'Communal Garden',
            'Roof Terrace', 'Balcony', 'Underground Parking', 'Driveway', 'Parking',
            'En suite', 'Video Entry', 'Double glazing', 'Conservatory', 'Concierge',
            'Close to public transport', 'Un-Furnished', 'Swimming Pool', '24 hour on-site security',
            'Receptionist', 'Meeting Room and Conference Facilities'
        ];
        $selectedFeatures = $property->features()->pluck('name')->toArray();
        $matches = $this->applicantMatcher->match($property);

        return view('properties.edit', compact('property', 'featuresList', 'selectedFeatures', 'matches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'postcode' => 'nullable|string',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'media.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'publish_to_portal' => 'boolean',
            'send_marketing_campaign' => 'boolean',
            'marketing_notes' => 'nullable|string',
        ]);
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('properties', 'public');
        }
        if (auth()->user() && auth()->user()->is_admin) {
            $validated['vendor_id'] = $request->input('vendor_id');
            $validated['landlord_id'] = $request->input('landlord_id');
            $validated['applicant_id'] = $request->input('applicant_id');
            $validated['notes'] = $request->input('notes');
            if ($request->hasFile('document')) {
                $validated['document'] = $request->file('document')->store('property_docs', 'public');
            }
        }
        $validated['publish_to_portal'] = $request->boolean('publish_to_portal');
        $validated['send_marketing_campaign'] = $request->boolean('send_marketing_campaign');
        $marketingNotes = trim($validated['marketing_notes'] ?? '');
        unset($validated['marketing_notes']);
        if (!empty($validated['address']) || !empty($validated['city']) || !empty($validated['postcode'])) {
            $coords = $this->geocodeAddress(trim(($validated['address'] ?? '') . ' ' . ($validated['city'] ?? '') . ' ' . ($validated['postcode'] ?? '')));
            if ($coords) {
                $validated['latitude'] = $coords['lat'];
                $validated['longitude'] = $coords['lng'];
            }
        }
        if ($marketingNotes !== '') {
            $log = $property->activity_log ?? [];
            $log['marketing_notes'][] = [
                'note' => $marketingNotes,
                'recorded_at' => now()->toIso8601String(),
                'author_id' => auth()->id(),
            ];
            $validated['activity_log'] = $log;
        }
        $property->update($validated);

        if ($request->hasFile('media')) {
            $order = $property->media()->max('order') ?? 0;
            foreach ($request->file('media') as $file) {
                $order++;
                $path = Storage::disk('public')->putFile('property_media', $file);
                $media = $property->media()->create([
                    'file_path' => $path,
                    'type' => $file->getClientMimeType(),
                    'order' => $order,
                ]);

                if (! $property->media()->where('is_featured', true)->exists()) {
                    $media->update(['is_featured' => true]);
                }
            }
        }

        foreach ($request->input('media_order', []) as $mediaId => $order) {
            $media = $property->media()->whereKey($mediaId)->first();
            if ($media) {
                $media->update(['order' => (int) $order]);
            }
        }

        $featuredMediaId = $request->input('featured_media');
        if ($featuredMediaId && $property->media()->whereKey($featuredMediaId)->exists()) {
            $property->media()->update(['is_featured' => false]);
            $property->media()->whereKey($featuredMediaId)->update(['is_featured' => true]);
        } elseif (! $property->media()->where('is_featured', true)->exists()) {
            $firstMedia = $property->media()->orderBy('order')->first();
            if ($firstMedia) {
                $firstMedia->update(['is_featured' => true]);
            }
        }

        // Update features
        $property->features()->delete();
        $features = $request->input('features', []);
        foreach ($features as $feature) {
            PropertyFeature::create([
                'property_id' => $property->id,
                'name' => $feature,
                'value' => 1
            ]);
        }
        return redirect()->route('properties.index')->with('success', 'Property updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property)
    {
        $property->delete();

        return redirect()->route('properties.index')->with('success', 'Property deleted successfully.');
    }

    /**
     * Assign a landlord to a property.
     */
    public function assignLandlord(Request $request, Property $property)
    {
        $request->validate([
            'landlord_id' => 'required|exists:contacts,id',
        ]);
        $property->landlord_id = $request->landlord_id;
        $property->save();
        return redirect()->route('properties.show', $property)->with('success', 'Landlord assigned successfully.');
    }

    /**
     * Geocode an address to coordinates.
     */
    protected function geocodeAddress(string $address): ?array
    {
        $response = Http::withHeaders([
            // Nominatim requires a User-Agent header for API access
            'User-Agent' => 'Savarix',
        ])->get('https://nominatim.openstreetmap.org/search', [
            'q' => $address,
            'format' => 'json',
            'limit' => 1,
        ]);

        if ($response->successful() && isset($response[0])) {
            return [
                'lat' => $response[0]['lat'],
                'lng' => $response[0]['lon'],
            ];
        }

        return null;
    }
}
