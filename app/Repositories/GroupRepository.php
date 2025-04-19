<?php

namespace App\Repositories;

use App\Enums\GroupTypeEnum;
use App\Models\Group;
use Illuminate\Pagination\LengthAwarePaginator;

class GroupRepository
{
    public function getPaginatedGroups($userId, $search = null, $perPage = 15): LengthAwarePaginator
    {
        $query = Group::where('user_id', $userId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('groupID', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function getAllGroups()
    {
        return Group::all();
    }

    public function getGroupById($id)
    {
        return Group::with('contacts')->findOrFail($id);
    }

    public function createGroup(array $data)
    {
        $group = Group::create($data);

        if (isset($data['contact_ids']) && is_array($data['contact_ids'])) {
            $group->contacts()->attach($data['contact_ids']);
        }

        return $group;
    }

    public function updateGroup($id, array $data): bool
    {
        $group = Group::findOrFail($id);
        $updated = $group->update($data);

        if (isset($data['contact_ids']) && is_array($data['contact_ids'])) {
            $group->contacts()->sync($data['contact_ids']);
        }

        return $updated;
    }

    public function deleteGroup($id): bool
    {
        $group = Group::findOrFail($id);
        // The contacts relationship will be automatically detached thanks to the onDelete('cascade') in the migration
        return $group->delete();
    }
}
