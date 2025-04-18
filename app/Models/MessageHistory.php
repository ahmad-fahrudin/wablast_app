<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'message',
        'contact_id',
        'device_id',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
