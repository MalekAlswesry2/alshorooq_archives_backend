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
        'status', 
        'user_id', 
        'admin_id', 
        'custom_id',
        'receipt_number',
        'department',
        'system_receipt_number',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
