<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fill = [
        'title',
        'price_room',
        'commission_percentage',
        'date_start',
        'date_end',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function userEvents()
    {
        return $this->hasMany(RoomUser::class);
    }
   
}
