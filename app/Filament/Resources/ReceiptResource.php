<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceiptResource\Pages;
use App\Filament\Resources\ReceiptResource\RelationManagers;
use App\Models\Receipt;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('User info')
                ->schema([
                    TextEntry::make('custom_id'),
                    TextEntry::make('user.name'),
                    TextEntry::make('amount')->money('LYD'),
                    TextEntry::make('reference_number'),
                    TextEntry::make('bank.name'),
                    TextEntry::make('payment_method'),
                    TextEntry::make('check_number')
                ])->columns(3),
                Section::make('Recipt image')
                ->schema([
                    ImageEntry::make('image'),
                ])->columns(2)

            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('custom_id')
                ->label("ID"),
                TextColumn::make('user.name'),

                TextColumn::make('reference_number'),
                TextColumn::make('amount')->money('LYD'),
                TextColumn::make('bank.name'),
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
                
                SelectFilter::make('department')->relationship('department', 'name'),
                SelectFilter::make('branch')->relationship('branch', 'name'),

                SelectFilter::make('status')
                ->options(
                [
                    'received'=>'received',
                    'not_received'=>'not received'
                ]
                )->attribute('status'),
                SelectFilter::make('payment_method')
                ->options(
                [
                    'transfer'=>'transfer',
                    'cash'=>'cash'
                ]
                )->attribute('payment_method'),
                Filter::make('created_at')
                ->form([
                    DatePicker::make('created_from'),
                    DatePicker::make('created_until'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'view' => Pages\ViewReceipt::route('/{record}'),
            'edit' => Pages\EditReceipt::route('/{record}/edit'),
        ];
    }
}
