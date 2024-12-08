<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'user_id',
        'address',
        'system_market_number',
        'status', // حالة السوق

    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
