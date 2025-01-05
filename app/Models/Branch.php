<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'status'];


    public function department()
{
    return $this->belongsTo(Department::class);
}

public function receipts()
{
    return $this->hasMany(Receipt::class);
}

public function markets()
{
    return $this->hasMany(Market::class);
}

}
