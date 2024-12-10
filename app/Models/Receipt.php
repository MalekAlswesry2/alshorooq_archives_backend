<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;
    protected $fillable = [
        'market_id',
        'client_number',
        'reference_number',
        'amount',
        'payment_method',
        'check_number',
        'bank_id',
        'image',
        'status', // حالة الإيصال
    ];
    

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
