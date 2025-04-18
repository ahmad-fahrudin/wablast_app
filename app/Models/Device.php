<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'deviceID',
        'name',
        'phone',
        'quota',
        'expired_at',
        'is_connected',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'is_connected' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function autoReply()
    {
        return $this->hasOne(AutoReply::class);
    }

    public function messageHistory()
    {
        return $this->hasMany(MessageHistory::class);
    }
}
