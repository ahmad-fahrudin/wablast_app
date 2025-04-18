<?php

namespace App\Services;

use App\Models\Subscription;

class SubscriptionService
{
    public function getPlan()
    {
        return Subscription::with('details')
            ->where('is_active', true)
            ->orderBy('price')
            ->get();
    }

    public function getPlanById($id)
    {
        return Subscription::with('details')->findOrFail($id);
    }
}
