<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuctionHuiRoom extends Model
{
    use HasFactory;
    protected $table = 'auction_hui_room';
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
    protected $fillable = [
        'starting_price',
        'time_end',
        'room_id',
        'auction_price'
    ];
}
