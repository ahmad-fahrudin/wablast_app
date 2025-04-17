<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'subscribe_id',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',

    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscribe_id');
    }
}
