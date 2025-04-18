<?php

namespace App\Repositories;

use App\Models\Subscription;

class SubscriptionRepository
{
    public function getPlan()
    {
        return Subscription::all();
    }
}
