<?php

namespace App\Filament\Widgets;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class PanelChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';



    protected function getData(): array
    {

        $receipts = DB::table('receipts')
        ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
        ->whereYear('created_at', Carbon::now()->year) // تحديد السنة الحالية
        ->groupBy('month')
        ->orderBy('month')
        ->get();
    
    $monthlyCounts = array_fill(0, 12, 0); // 
    
    foreach ($receipts as $receipt) {
        $monthlyCounts[$receipt->month - 1] = $receipt->count; 
    }

        return [
            'datasets' => [
                [
                    'label' => 'Receipts created',
                    'data' => $monthlyCounts,
                    // 'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    
    }

    protected function getType(): string
    {
        return 'line';
    }
}
