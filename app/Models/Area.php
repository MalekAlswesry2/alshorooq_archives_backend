<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'zone_id'];

    // علاقة مع Zone
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }
}
