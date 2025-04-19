<?php

namespace App\Services;

use App\Enums\GroupTypeEnum;
use App\Repositories\GroupRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class GroupService
{
    protected $groupRepository;

    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function getPaginatedGroups($userId, $search = null, $perPage = 15): LengthAwarePaginator
    {
        return $this->groupRepository->getPaginatedGroups($userId, $search, $perPage);
    }

    public function getGroupById($id)
    {
        return $this->groupRepository->getGroupById($id);
    }

    public function createGroup($userId, $type, $subject, $description = null, $groupID = null, $contactIds = [])
    {
        $data = [
            'user_id' => $userId,
            'type' => $type,
            'subject' => $subject,
            'description' => $description,
            'groupID' => $groupID,
            'contact_ids' => $contactIds
        ];

        return $this->groupRepository->createGroup($data);
    }

    public function updateGroup($id, array $data): bool
    {
        return $this->groupRepository->updateGroup($id, $data);
    }

    public function deleteGroup($id): bool
    {
        return $this->groupRepository->deleteGroup($id);
    }
}
