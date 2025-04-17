<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'limit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function devices()
    {
        return $this->hasMany(Device::class, 'subscription_id');
    }

    public function details()
    {
        return $this->hasMany(SubscriptionDetail::class, 'subscribe_id');
    }

    public function messageHistory()
    {
        return $this->hasMany(MessageHistory::class, 'subscribe_id');
    }

    public function subscriptionUsers()
    {
        return $this->hasMany(SubscriptionUser::class, 'subscribe_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'subscribe_id');
    }
}
