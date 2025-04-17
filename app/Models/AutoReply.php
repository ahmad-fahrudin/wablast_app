<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'message',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
