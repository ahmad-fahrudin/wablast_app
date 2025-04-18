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
        'quota',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function details()
    {
        return $this->hasMany(SubscriptionDetail::class);
    }

    public function messageHistory()
    {
        return $this->hasMany(MessageHistory::class);
    }

    public function subscriptionDevice()
    {
        return $this->hasMany(DeviceSubscription::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
