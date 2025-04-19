<?php

namespace App\Services;

use App\Repositories\ContactRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ContactService
{
    protected $contactRepository;

    public function __construct(ContactRepository $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    public function getPaginatedContacts($userId, $search = null, $perPage = 15): LengthAwarePaginator
    {
        return $this->contactRepository->getPaginatedContacts($userId, $search, $perPage);
    }

    public function getAllContacts()
    {
        return $this->contactRepository->getAllContacts();
    }

    public function getContactById($id)
    {
        return $this->contactRepository->getContactById($id);
    }

    public function createContact($userId, $name, $phone, $isActive = true)
    {
        $data = [
            'user_id' => $userId,
            'name' => $name,
            'phone' => $phone,
            'is_active' => $isActive,
        ];

        return $this->contactRepository->createContact($data);
    }

    public function updateContact($id, array $data): bool
    {
        return $this->contactRepository->updateContact($id, $data);
    }

    public function deleteContact($id): bool
    {
        return $this->contactRepository->deleteContact($id);
    }
}
