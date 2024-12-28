<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceiptResource\Pages;
use App\Filament\Resources\ReceiptResource\RelationManagers;
use App\Models\Receipt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('custom_id'),
                TextColumn::make('reference_number'),
                TextColumn::make('amount')->money('LYD'),
                TextColumn::make('bank.name'),
                TextColumn::make('user.name'),
                TextColumn::make('payment_method'),
                TextColumn::make('check_number'),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state){
                    'not_received' => 'warning',
                    'received' => 'success',
                }),
                ImageColumn::make('image')->square()->visibility('private')
                ->openUrlInNewTab(),
                // ImageColumn::make('image')->defaultImageUrl(url('/images/placeholder.png')),


            ])
            ->filters([
                SelectFilter::make('status')
                ->options(
                   [
                    'received'=>'received',
                    'not_received'=>'not received'
                   ]
                )->attribute('status')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReceipts::route('/'),
            'create' => Pages\CreateReceipt::route('/create'),
            'edit' => Pages\EditReceipt::route('/{record}/edit'),
        ];
    }
}
