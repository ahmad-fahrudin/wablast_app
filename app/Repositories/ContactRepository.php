<?php

namespace App\Repositories;

use App\Models\Contact;
use Illuminate\Pagination\LengthAwarePaginator;

class ContactRepository
{
    public function getPaginatedContacts($userId, $search = null, $perPage = 15): LengthAwarePaginator
    {
        $query = Contact::where('user_id', $userId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getContactById($id): ?Contact
    {
        return Contact::find($id);
    }

    public function getAllContacts()
    {
        return Contact::all();
    }

    public function createContact(array $data): Contact
    {
        return Contact::create($data);
    }

    public function updateContact($id, array $data): bool
    {
        $contact = $this->getContactById($id);

        if (!$contact) {
            return false;
        }

        return $contact->update($data);
    }

    public function deleteContact($id): bool
    {
        $contact = $this->getContactById($id);

        if (!$contact) {
            return false;
        }

        return $contact->delete();
    }
}
