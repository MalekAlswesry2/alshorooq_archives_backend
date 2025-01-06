<?php

namespace App\Filament\Widgets;

use App\Models\Receipt;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Receipts', Receipt::query()->count())
            ->description('Total Receipts in the system'),
            Stat::make('Users', User::query()->count())
            ->description('All Users in the system'),
            Stat::make('Admins', User::query()->count())
            ->description('All admins in the system'),

        ];
    }
}
