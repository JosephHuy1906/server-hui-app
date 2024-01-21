<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public function roomUser()
    {
        return $this->belongsTo(RoomUser::class, 'room_user_id');
    }
    protected $fillable = [
        'id',
        'room_user_id',
        'user_id',
        'status',
        'description',
        'price_pay',
        'room_id'
    ];
}
