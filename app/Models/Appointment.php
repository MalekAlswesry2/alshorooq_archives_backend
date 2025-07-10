<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\AppointmentStatus;


class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'market_id',
        'scheduled_at',
        'description',
        'status',
    ];
    protected $casts = [
        'scheduled_at' => 'datetime',
        'status' => AppointmentStatus::class,
    ];    

    use HasFactory;

    public function user()
{
    return $this->belongsTo(User::class);
}

public function market()
{
    return $this->belongsTo(Market::class);
}

public function getStatusObjectAttribute(): array
    {
        $actualStatus = $this->status;

    if (
        $actualStatus === AppointmentStatus::Upcoming &&
        $this->scheduled_at->isBefore(now()->subHours(24))
    ) {
        $actualStatus = AppointmentStatus::NotCompleted;
    }

    return [
        'key' => $actualStatus->value,
        'label' => $actualStatus->label(),
    ];
        // return [
        //     'key' => $this->status->value,
        //     'label' => $this->status->label(),
        // ];
    }

}
