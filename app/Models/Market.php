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
        'status',
        'branch_id',
        'department_id',
        'area_id'
    ];

protected $casts = [
    'id' => 'integer',
    'user_id' => 'integer',
    'branch_id' => 'integer',
    'department_id' => 'integer',
];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function branch()
{
    return $this->belongsTo(Branch::class);
}
public function area()
{
    return $this->belongsTo(Area::class);
}
public function appointments()
{
    return $this->hasMany(Appointment::class);
}

}

