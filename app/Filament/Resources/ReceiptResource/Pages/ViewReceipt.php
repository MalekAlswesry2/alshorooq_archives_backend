<?php

namespace App\Filament\Resources\ReceiptResource\Pages;

use App\Filament\Resources\ReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
class ViewReceipt extends ViewRecord
{
    protected static string $resource = ReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('custom_id')
            ->label("ID"),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            // ...
        ]);
}
}
