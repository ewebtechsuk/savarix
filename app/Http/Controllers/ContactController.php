<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\ContactTag;
use App\Models\ContactNote;
use App\Models\ContactCommunication;
use App\Models\ContactViewing;
use App\Models\Offer;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $baseQuery = Contact::query();

        $filters = $request->only(['search', 'type', 'group', 'tag']);
        $filteredQuery = clone $baseQuery;

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $filteredQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $filteredQuery->where('type', $filters['type']);
        }

        if (!empty($filters['group']) && $filters['group'] !== 'all') {
            $filteredQuery->whereHas('groups', function ($relation) use ($filters) {
                $relation->where('contact_groups.id', $filters['group']);
            });
        }

        if (!empty($filters['tag']) && $filters['tag'] !== 'all') {
            $filteredQuery->whereHas('tags', function ($relation) use ($filters) {
                $relation->where('tags.id', $filters['tag']);
            });
        }

        $contacts = (clone $filteredQuery)
            ->with(['groups', 'tags'])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $typeBreakdown = Contact::query()
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->orderBy('type')
            ->pluck('total', 'type');

        $groupBreakdown = ContactGroup::query()
            ->withCount(['contacts'])
            ->orderBy('name')
            ->get();

        $tagBreakdown = ContactTag::query()
            ->withCount(['contacts'])
            ->orderBy('name')
            ->get();

        $totals = [
            'overall' => $baseQuery->count(),
            'filtered' => $contacts->total(),
        ];

        $types = Contact::TYPES;

        return view('contacts.index', compact(
            'contacts',
            'filters',
            'types',
            'typeBreakdown',
            'groupBreakdown',
            'tagBreakdown',
            'totals'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $allGroups = ContactGroup::orderBy('name')->get();
        $allTags = ContactTag::orderBy('name')->get();
        $types = Contact::TYPES;
        return view('contacts.create', compact('allGroups', 'allTags', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->filled('name') && ($request->filled('first_name') || $request->filled('last_name'))) {
            $request->merge([
                'name' => trim($request->input('first_name', '') . ' ' . $request->input('last_name', '')),
            ]);
        }

        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:120',
            'last_name' => 'nullable|string|max:120',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $contact = Contact::create($validated);
        $contact->groups()->sync($request->input('groups', []));
        $contact->tags()->sync($request->input('tags', []));
        return redirect()->route('contacts.index')->with('success', 'Contact created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        $contact->load([
            'groups',
            'tags',
            'properties',
            'notes',
            'communications',
            'viewings.property',
            'offers.property',
            'tenancies.property',
        ]);
        return view('contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
        $allGroups = ContactGroup::orderBy('name')->get();
        $allTags = ContactTag::orderBy('name')->get();
        $types = Contact::TYPES;
        $contact->load('groups', 'tags');
        return view('contacts.edit', compact('contact', 'allGroups', 'allTags', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        if (!$request->filled('name') && ($request->filled('first_name') || $request->filled('last_name'))) {
            $request->merge([
                'name' => trim($request->input('first_name', '') . ' ' . $request->input('last_name', '')),
            ]);
        }

        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:120',
            'last_name' => 'nullable|string|max:120',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $contact->update($validated);
        $contact->groups()->sync($request->input('groups', []));
        $contact->tags()->sync($request->input('tags', []));
        return redirect()->route('contacts.index')->with('success', 'Contact updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully.');
    }

    /**
     * Search for contacts by type and name.
     */
    public function search(Request $request)
    {
        $type = $request->get('type', 'landlord');
        $q = $request->get('q', '');
        $results = Contact::query()
            ->where('type', $type)
            ->where('name', 'like', "%$q%")
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name']);
        return response()->json($results);
    }

    public function searchProperties(Request $request)
    {
        $tenant = tenant();
        $q = $request->get('q', '');
        $onlyUnassigned = $request->boolean('unassigned', true);

        $query = Property::query();

        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        }

        if ($onlyUnassigned) {
            $query->whereNull('landlord_id');
        }

        if ($q) {
            $query->where(function ($builder) use ($q) {
                $builder->where('title', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%")
                    ->orWhere('postcode', 'like', "%{$q}%");
            });
        }

        $properties = $query
            ->orderBy('title')
            ->limit(15)
            ->get(['id', 'title', 'address']);

        return response()->json($properties->map(function ($property) {
            return [
                'id' => $property->id,
                'text' => trim($property->title . ' — ' . $property->address),
            ];
        }));
    }

    /**
     * Bulk actions for contacts.
     */
    public function bulk(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('contacts', []);
        if (!$action || empty($ids)) {
            return back()->with('error', 'No action or contacts selected.');
        }
        $contactsQuery = Contact::query()->whereIn('id', $ids);
        $contacts = $contactsQuery->get();
        if ($action === 'delete') {
            foreach ($contacts as $contact) {
                $contact->delete();
            }
            return back()->with('success', 'Contacts deleted.');
        } elseif ($action === 'tag') {
            $tagId = $request->input('tag');
            if ($tagId) {
                foreach ($contacts as $contact) {
                    $contact->tags()->syncWithoutDetaching([$tagId]);
                }
                return back()->with('success', 'Tag added to selected contacts.');
            }
        } elseif ($action === 'email') {
            $subject = $request->input('subject', 'Message from SAVIRIX');
            $body = $request->input('body', 'This is a bulk message.');
            foreach ($contacts as $contact) {
                if ($contact->email) {
                    Mail::raw($body, function ($message) use ($contact, $subject) {
                        $message->to($contact->email)->subject($subject);
                    });
                }
            }
            return back()->with('success', 'Bulk email sent.');
        } elseif ($action === 'sms') {
            $smsBody = $request->input('sms_body', 'This is a bulk SMS.');
            // Example: integrate with Twilio or other SMS provider here
            foreach ($contacts as $contact) {
                if ($contact->phone) {
                    // Replace with real SMS sending logic
                    // SmsService::send($contact->phone, $smsBody);
                }
            }
            return back()->with('success', 'Bulk SMS sent (mocked).');
        }
        return back()->with('error', 'Invalid action.');
    }

    /**
     * Add a note to a contact.
     */
    public function addNote(Request $request, Contact $contact)
    {
        $request->validate([
            'note' => 'required|string|max:1000',
        ]);
        $contact->notes()->create([
            'note' => $request->note,
            'user_id' => auth()->id(),
        ]);
        return redirect()->route('contacts.show', $contact)->with('success', 'Note added.');
    }

    /**
     * Add a communication to a contact.
     */
    public function addCommunication(Request $request, Contact $contact)
    {
        $request->validate([
            'communication' => 'required|string|max:1000',
        ]);
        $contact->communications()->create([
            'communication' => $request->communication,
            'user_id' => auth()->id(),
        ]);
        return redirect()->route('contacts.show', $contact)->with('success', 'Communication added.');
    }

    /**
     * Add a viewing to a contact.
     */
    public function addViewing(Request $request, Contact $contact)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'date' => 'required|date',
        ]);
        $property = Property::findOrFail($request->property_id);
        if ($tenant = tenant()) {
            if ($property->tenant_id && $property->tenant_id !== $tenant->id) {
                abort(403, 'Property does not belong to this tenant.');
            }
        }

        $requestedStart = Carbon::parse($request->date);
        $conflict = $contact->viewings()
            ->whereBetween('date', [$requestedStart->copy()->subMinutes(30), $requestedStart->copy()->addMinutes(30)])
            ->exists();

        if ($conflict) {
            return redirect()
                ->back()
                ->withErrors(['date' => 'This viewing overlaps with another scheduled slot for this contact.'])
                ->withInput();
        }

        $contact->viewings()->create([
            'property_id' => $property->id,
            'date' => $request->date,
            'user_id' => auth()->id(),
        ]);
        return redirect()->route('contacts.show', $contact)->with('success', 'Viewing added.');
    }

    /**
     * Delete a note from a contact.
     */
    public function deleteNote(Contact $contact, $noteId)
    {
        $note = $contact->notes()->findOrFail($noteId);
        $note->delete();
        return redirect()->route('contacts.show', $contact)->with('success', 'Note deleted.');
    }

    /**
     * Delete a communication from a contact.
     */
    public function deleteCommunication(Contact $contact, $communicationId)
    {
        $comm = $contact->communications()->findOrFail($communicationId);
        $comm->delete();
        return redirect()->route('contacts.show', $contact)->with('success', 'Communication deleted.');
    }

    /**
     * Delete a viewing from a contact.
     */
    public function deleteViewing(Contact $contact, $viewingId)
    {
        $viewing = $contact->viewings()->findOrFail($viewingId);
        $viewing->delete();
        return redirect()->route('contacts.show', $contact)->with('success', 'Viewing deleted.');
    }

    /**
     * Edit a note for a contact.
     */
    public function editNote(Contact $contact, $noteId)
    {
        $note = $contact->notes()->findOrFail($noteId);
        return view('contacts.partials.edit_note', compact('contact', 'note'));
    }

    /**
     * Update a note for a contact.
     */
    public function updateNote(Request $request, Contact $contact, $noteId)
    {
        $note = $contact->notes()->findOrFail($noteId);
        $request->validate(['note' => 'required|string|max:1000']);
        $note->update(['note' => $request->note]);
        return redirect()->route('contacts.show', $contact)->with('success', 'Note updated.');
    }

    /**
     * Edit a communication for a contact.
     */
    public function editCommunication(Contact $contact, $communicationId)
    {
        $comm = $contact->communications()->findOrFail($communicationId);
        return view('contacts.partials.edit_communication', compact('contact', 'comm'));
    }

    /**
     * Update a communication for a contact.
     */
    public function updateCommunication(Request $request, Contact $contact, $communicationId)
    {
        $comm = $contact->communications()->findOrFail($communicationId);
        $request->validate(['communication' => 'required|string|max:1000']);
        $comm->update(['communication' => $request->communication]);
        return redirect()->route('contacts.show', $contact)->with('success', 'Communication updated.');
    }

    /**
     * Edit a viewing for a contact.
     */
    public function editViewing(Contact $contact, $viewingId)
    {
        $viewing = $contact->viewings()->findOrFail($viewingId);
        return view('contacts.partials.edit_viewing', compact('contact', 'viewing'));
    }

    /**
     * Update a viewing for a contact.
     */
    public function updateViewing(Request $request, Contact $contact, $viewingId)
    {
        $viewing = $contact->viewings()->findOrFail($viewingId);
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'date' => 'required|date',
        ]);
        $property = Property::findOrFail($request->property_id);
        if ($tenant = tenant()) {
            if ($property->tenant_id && $property->tenant_id !== $tenant->id) {
                abort(403, 'Property does not belong to this tenant.');
            }
        }
        $viewing->update([
            'property_id' => $property->id,
            'date' => $request->date,
        ]);
        return redirect()->route('contacts.show', $contact)->with('success', 'Viewing updated.');
    }

    public function assignProperty(Request $request, Contact $contact)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        if ($contact->type !== 'landlord') {
            return back()->with('error', 'Only landlord contacts can be assigned properties.');
        }

        $property = Property::findOrFail($request->property_id);
        if ($tenant = tenant()) {
            if ($property->tenant_id && $property->tenant_id !== $tenant->id) {
                abort(403, 'Property does not belong to this tenant.');
            }
        }

        $property->landlord_id = $contact->id;
        $property->save();

        return redirect()->route('contacts.show', $contact)->with('success', 'Property assigned successfully.');
    }

    /**
     * API Methods
     */

    public function apiUpdateNote(Request $request, Contact $contact, $noteId)
    {
        $note = $contact->notes()->findOrFail($noteId);
        $request->validate(['note' => 'required|string|max:1000']);
        $note->update(['note' => $request->note]);
        return response()->json(['success' => true, 'note' => $note->note]);
    }

    public function apiDeleteNote(Contact $contact, $noteId)
    {
        $note = $contact->notes()->findOrFail($noteId);
        $note->delete();
        return response()->json(['success' => true]);
    }

    public function apiUpdateCommunication(Request $request, Contact $contact, $communicationId)
    {
        $comm = $contact->communications()->findOrFail($communicationId);
        $request->validate(['communication' => 'required|string|max:1000']);
        $comm->update(['communication' => $request->communication]);
        return response()->json(['success' => true, 'communication' => $comm->communication]);
    }

    public function apiDeleteCommunication(Contact $contact, $communicationId)
    {
        $comm = $contact->communications()->findOrFail($communicationId);
        $comm->delete();
        return response()->json(['success' => true]);
    }

    public function apiUpdateViewing(Request $request, Contact $contact, $viewingId)
    {
        $viewing = $contact->viewings()->findOrFail($viewingId);
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'date' => 'required|date',
        ]);
        $property = Property::findOrFail($request->property_id);
        if ($tenant = tenant()) {
            if ($property->tenant_id && $property->tenant_id !== $tenant->id) {
                abort(403, 'Property does not belong to this tenant.');
            }
        }
        $viewing->update([
            'property_id' => $property->id,
            'date' => $request->date,
        ]);
        return response()->json(['success' => true, 'property_id' => $viewing->property_id, 'date' => $viewing->date]);
    }

    public function apiDeleteViewing(Contact $contact, $viewingId)
    {
        $viewing = $contact->viewings()->findOrFail($viewingId);
        $viewing->delete();
        return response()->json(['success' => true]);
    }
}
