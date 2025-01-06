<?php

namespace App\Filament\Widgets;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ReceiptsChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        // جلب عدد الإيصالات المستلمة وغير المستلمة
        $receipts = DB::table('receipts')
            ->select(DB::raw('status'), DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
    
        // مصفوفة لعدد الإيصالات بناءً على الحالة
        $receivedCount = 0;
        $notReceivedCount = 0;
    
        foreach ($receipts as $receipt) {
            if ($receipt->status === 'received') {
                $receivedCount = $receipt->count;
            } elseif ($receipt->status === 'not_received') {
                $notReceivedCount = $receipt->count;
            }
        }
    
        return [
            'datasets' => [
                [
                    'label' => 'Receipts Status',
                    'data' => [$receivedCount, $notReceivedCount],
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',  // لون للإيصالات المستلمة
                        'rgb(54, 162, 235)',  // لون للإيصالات غير المستلمة
                    ],
                    'borderColor' => '#9BD0F5',
                ],
            ],
            'labels' => [
                'Received',
                'Not Received',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
