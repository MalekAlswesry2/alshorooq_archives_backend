<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'logo',


    ];

    public function isActive()
    {
        return $this->status === 'active';
    }

    protected $casts = [
        'status' => 'string',
    ];

    public function branches()
{
    return $this->hasMany(Branch::class);
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
