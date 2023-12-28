<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    use HasFactory;
    protected $table = 'check_out';
    protected $fillable = [
        'user_id',
        'price',
        'bank_code',
        'description',
        'status'
    ];
}
