<?php

namespace App\Filament\Resources\ReceiptResource\Pages;

use App\Filament\Resources\ReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListReceipts extends ListRecords
{
    protected static string $resource = ReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            // 'all' => Tab::make()
            // ->modifyQueryUsing(fn (Builder $query) => $query->where()),
            'received' => Tab::make()
            
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'received'))
                // ->badge(fn () => Receipt::where('status', 'received')->count())
                
                ,
                
            'not_received' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'not_received'))
                // ->badge(fn () => Receipt::where('status', 'not_received')->count())
                ,

        ];
    }
}
