<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'subscription_id',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
