<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'market_id',
        'scheduled_at',
        'description',
        'status',
    ];
    protected $casts = [
        'scheduled_at' => 'datetime',
    ];    

    use HasFactory;

    public function user()
{
    return $this->belongsTo(User::class);
}

public function market()
{
    return $this->belongsTo(Market::class);
}

}
