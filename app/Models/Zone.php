<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code'];

    public function areas()
    {
        return $this->hasMany(Area::class, 'zone_id');
    }
    
}
