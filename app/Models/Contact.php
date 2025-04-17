<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messageHistory()
    {
        return $this->hasMany(MessageHistory::class, 'contact_id');
    }
}
