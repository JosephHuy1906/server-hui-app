<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWinHui extends Model
{
    use HasFactory;
    protected $table = 'user_win_hui';

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    protected $fillable = [
        'id',
        'user_id',
        'commission_percentage',
        'price_pay_hui',
        'total_auction',
        'room_id',
        'status_user',
        'status_admin',
        'total_money_received',
        'total_amount_payable',
    ];
}
