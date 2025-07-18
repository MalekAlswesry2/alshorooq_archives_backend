<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'key',
        'name',
        'icon',
        'state',
        'is_active',
        'route',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
