<?php

namespace App\Http\Controllers;

use App\Enums\GroupTypeEnum;
use App\Models\Contact;
use App\Services\GroupService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GroupController extends Controller
{
    protected $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    public function index(Request $request)
    {
        $groups = $this->groupService->getPaginatedGroups(
            auth()->id(),
            $request->search
        );

        return Inertia::render('group/Index', [
            'groups' => $groups,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function create()
    {
        $contacts = Contact::where('user_id', auth()->id())->get();

        return Inertia::render('group/Create', [
            'contacts' => $contacts,
            'groupTypes' => collect(GroupTypeEnum::cases())->map(fn($type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:' . implode(',', array_column(GroupTypeEnum::cases(), 'value')),
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'groupID' => 'nullable|string|max:255',
            'contact_ids' => 'array',
            'contact_ids.*' => 'exists:contacts,id',
        ]);

        $this->groupService->createGroup(
            auth()->id(),
            $request->type,
            $request->subject,
            $request->description,
            $request->groupID,
            $request->contact_ids ?? []
        );

        return redirect()->route('groups.index')->with('success', 'Group created successfully');
    }

    public function edit($id)
    {
        $group = $this->groupService->getGroupById($id);
        $contacts = Contact::where('user_id', auth()->id())->get();

        return Inertia::render('group/Edit', [
            'group' => $group,
            'contacts' => $contacts,
            'groupTypes' => collect(GroupTypeEnum::cases())->map(fn($type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ]),
            'selectedContacts' => $group->contacts->pluck('id'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|string|in:' . implode(',', array_column(GroupTypeEnum::cases(), 'value')),
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'groupID' => 'nullable|string|max:255',
            'contact_ids' => 'array',
            'contact_ids.*' => 'exists:contacts,id',
        ]);

        $this->groupService->updateGroup($id, [
            'type' => $request->type,
            'subject' => $request->subject,
            'description' => $request->description,
            'groupID' => $request->groupID,
            'contact_ids' => $request->contact_ids ?? [],
        ]);

        return redirect()->route('groups.index')->with('success', 'Group updated successfully');
    }

    public function destroy($id)
    {
        $this->groupService->deleteGroup($id);

        return redirect()->route('groups.index')->with('success', 'Group deleted successfully');
    }
}
