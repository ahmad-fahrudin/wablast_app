<?php

namespace App\Http\Controllers;

use App\Services\ContactService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    protected $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $contacts = $this->contactService->getPaginatedContacts(
            auth()->id(),
            $search,
            15
        );

        return Inertia::render('contact/Index', [
            'contacts' => $contacts,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * API endpoint to get paginated contacts for selector
     */
    public function getContactsForSelector(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        $contacts = $this->contactService->getPaginatedContacts(
            auth()->id(),
            $search,
            $perPage
        );

        return response()->json($contacts);
    }

    public function create()
    {
        return Inertia::render('contact/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'is_active' => 'boolean',
        ]);

        $this->contactService->createContact(
            auth()->id(),
            $validated['name'],
            $validated['phone'],
            $validated['is_active'] ?? true
        );

        return redirect()->route('contacts.index');
    }

    public function edit($id)
    {
        $contact = $this->contactService->getContactById($id);

        if (!$contact || $contact->user_id !== auth()->id()) {
            abort(404);
        }

        return Inertia::render('contact/Edit', ['contact' => $contact]);
    }

    public function update(Request $request, $id)
    {
        $contact = $this->contactService->getContactById($id);

        if (!$contact || $contact->user_id !== auth()->id()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'is_active' => 'boolean',
        ]);

        $this->contactService->updateContact($id, $validated);

        return redirect()->route('contacts.index');
    }

    public function destroy($id)
    {
        $contact = $this->contactService->getContactById($id);

        if (!$contact || $contact->user_id !== auth()->id()) {
            abort(404);
        }

        $this->contactService->deleteContact($id);

        return redirect()->route('contacts.index');
    }
}
