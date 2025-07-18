<?php
namespace App\Filament\Widgets;
use App\Models\Appointment;
use App\Models\Receipt;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();

        return [

        Stat::make('الوقت الآن', now()->format('Y-m-d H:i'))
            ->description('تاريخ ووقت النظام الحالي'),

        Stat::make('إيصالات غير مستلمة اليوم', Receipt::query()
                ->whereDate('created_at', $today)
                ->where('status', 'not_received')
                ->count()),
            // ->description('الإيصالات التي لم تُستلم اليوم'),

        Stat::make('إيصالات مستلمة اليوم', Receipt::query()
                ->whereDate('created_at', $today)
                ->where('status', 'received')
                ->count()),
            // ->description('الإيصالات التي تم استلامها اليوم'),

        Stat::make('مواعيد تمت زيارتها اليوم', Appointment::query()
                ->whereDate('scheduled_at', $today)
                ->where('status', 'completed') // غيّرها حسب اسم الحالة لديك
                ->count())
            // ->description('المواعيد التي تمت زيارتها اليوم'),
    ];

    }
}
