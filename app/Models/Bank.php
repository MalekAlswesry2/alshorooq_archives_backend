<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'account_number',
        'branch',
        'branch_id',
        'status', 
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function branch()
{
    return $this->belongsTo(Branch::class);
}
}
