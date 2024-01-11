<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class BankAccount extends Model
{
    use HasFactory, HasUuids, Notifiable;

    protected $table = 'bank_account';
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    protected $fillable = [
        'user_id',
        'short_name',
        'number_bank',
        'code',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = str::uuid();
        });
    }
}
