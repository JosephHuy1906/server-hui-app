<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuctionHuiDetail extends Model
{
    use HasFactory;
    protected $table = 'auction_hui_detail';
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function AuctionHuiRoom()
    {
        return $this->belongsTo(AuctionHuiRoom::class, 'auction_hui_id');
    }
    protected $fillable = [
        'auction_hui_id',
        'user_id',
        'starting_price',
        'auction_percentage',
        'total_price',
    ];
}
