<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'price_room',
        'avatar',
        'commission_percentage',
        'payment_time',
        'date_room_end',
        'total_user',
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
