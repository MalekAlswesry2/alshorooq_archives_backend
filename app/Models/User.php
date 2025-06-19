<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
// class User extends Authenticatable 
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'department_id',
        'branch_id',
        'zone_id',
        'email',
        'status',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'balance' => 'double', 

    ];

    //     public function zones()
    // {
    //     return $this->belongsToMany(Zone::class);
    // }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
        public function department()
        {
            return $this->belongsTo(Department::class);
        }
    
        public function branch()
        {
            return $this->belongsTo(Branch::class);
        }
        public function branches()
        {
            return $this->belongsToMany(Branch::class);
        }
        
        public function departments()
        {
            return $this->belongsToMany(Department::class);
        }
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    public function hasPermission($permission)
    {
        // return $this->permissions->contains('key', $permission);
        return $this->permissions()->where('key', $permission)->exists();

    }

    public function markets()
{
    return $this->hasMany(Market::class, 'user_id');
}

public function receipts()
{
    return $this->hasMany(Receipt::class, 'user_id');
}

public function appointments()
{
    return $this->hasMany(Appointment::class);
}


public function canAccessPanel(Panel $panel): bool
{
    // return str_ends_with($this->email, '@gmail.com') && $this->hasVerifiedEmail();
    return str_ends_with($this->email, '@gmail.com');
}

}
