<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


    // public function getTabs(): array
    // {
    //     return [
    //         // 'all' => Tab::make()
    //         // ->modifyQueryUsing(fn (Builder $query) => $query->where()),
    //         'المشرفين' => Tab::make()

    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'admin'))
    //             // ->badge(fn () => Receipt::where('status', 'received')->count())

    //             ,

    //         'المستخدمين' => Tab::make()
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'user'))
    //             // ->badge(fn () => Receipt::where('status', 'not_received')->count())
    //             ,

    //     ];
    // }

    // protected function getStats(): array
    // {
    //     return [
    //         Stat::make('Unique views', '192.1k'),
    //         Stat::make('Bounce rate', '21%'),
    //         Stat::make('Average time on page', '3:12'),
    //     ];
    // }

}
