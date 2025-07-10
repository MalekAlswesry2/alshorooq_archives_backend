<?php

// app/Enums/AppointmentStatus.php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Upcoming = 'upcoming';
    case Completed = 'completed';
    case NotCompleted = 'not_completed';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match($this) {
            self::Upcoming => __('appointment.status.upcoming'),
            self::Completed => __('appointment.status.completed'),
            self::NotCompleted => __('appointment.status.not_completed'),
            self::Canceled => __('appointment.status.canceled'),
        };
    }

    public static function options(): array
    {
        return array_map(
            fn($case) => [
                'key' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }
}


