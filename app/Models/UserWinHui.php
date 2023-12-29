<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWinHui extends Model
{
    use HasFactory;
    protected $table = 'user_win_hui';
    protected $fillable = [
        'id',
        'user_id',
        'commission_percentage',
        'price_pay_hui',
        'total_auction',
        'room_id',
        'status',
        'total_money_received',
        'total_amount_payable',
    ];
}
