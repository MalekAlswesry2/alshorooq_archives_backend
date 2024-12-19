<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'receipt_id',
        'type',
        'amount',
        'balance_after',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }
}
