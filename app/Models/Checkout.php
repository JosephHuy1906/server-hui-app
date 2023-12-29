<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    use HasFactory;
    protected $table = 'check_out';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    protected $fillable = [
        'id',
        'user_id',
        'price',
        'description',
        'user_win_hui_id',
        'status'
    ];
}
