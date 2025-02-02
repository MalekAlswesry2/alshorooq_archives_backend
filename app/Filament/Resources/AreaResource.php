<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AreaResource\Pages;
use App\Filament\Resources\AreaResource\RelationManagers;
use App\Models\Area;
use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = "المناطق وخطوط السير";
    protected static ?string $label = "منطقة";
    protected static ?string $navigationLabel = "مناطق";
    protected static ?string $modelLabel = "منطقة";
    protected static ?string $pluralLabel = "المناطق";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->label("اسم المنطقة")
                ->required()
                ->maxLength(255),
                Select::make('zone_id')
                ->label('خط السير')
                ->options(Zone::all()->pluck('name', 'id'))
                ->searchable()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->label("اسم المنطقة")
                ->searchable(),
                TextColumn::make('zone.name')
                ->label("خط السير")
            ,

            ])
            ->filters([
                SelectFilter::make('zone_id')->label("خط السير")

                ->options(
                    Zone::all()->pluck('name', 'id')
                )->attribute('zone_id')
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
            'index' => Pages\ListAreas::route('/'),
            'create' => Pages\CreateArea::route('/create'),
            'edit' => Pages\EditArea::route('/{record}/edit'),
        ];
    }
}
