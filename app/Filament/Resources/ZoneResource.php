<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ZoneResource\Pages;
use App\Filament\Resources\ZoneResource\RelationManagers;
use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ZoneResource extends Resource
{
    protected static ?string $model = Zone::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationGroup = "المناطق وخطوط السير";
    protected static ?string $label = "خط السير";
        protected static ?int $navigationSort = 9;
    protected static ?string $navigationLabel = "خطوط السير";
    protected static ?string $modelLabel = "خط السير";
        protected static ?string $pluralLabel = "خطوط السير";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label("اسم خط السير")
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->label("كود خط السير")
                    ->maxLength(255),
                    Select::make('branch_id')
                    ->label('الفرع ')
                    ->relationship('branch', 'name')
                    ->preload()
                    ->searchable()
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->label("اسم خط السير")
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                ->label("كود خط السير")
                    ->searchable(),
                    Tables\Columns\TextColumn::make('branch.name')
                    ->label("الفرع")
                        ->searchable(),

            ])
            ->filters([
                //
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
            'index' => Pages\ListZones::route('/'),
            'create' => Pages\CreateZone::route('/create'),
            'edit' => Pages\EditZone::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('zones_view');
    }

//     public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
// {
//     $user = auth()->user();

//     $query = parent::getEloquentQuery();

//     // فقط للإدمن نفعّل الفلترة
//     if ($user->role !== 'admin') {
//         return $query;
//     }

//     $branchIds = $user->branches()->pluck('branches.id')->toArray();
//     $departmentIds = $user->departments()->pluck('departments.id')->toArray();
//     $zoneIds = $user->zones()->pluck('zones.id')->toArray(); // يجب أن تكون علاقة zones موجودة

//     return $query
//         ->when(!empty($branchIds), fn($q) => $q->whereIn('branch_id', $branchIds),
//             fn($q) => $q->where('branch_id', $user->branch_id)
//         )
//         ->when(!empty($departmentIds), fn($q) => $q->whereIn('department_id', $departmentIds),
//             fn($q) => $q->where('department_id', $user->department_id)
//         )
//         ->when(!empty($zoneIds), fn($q) => $q->whereIn('zone_id', $zoneIds),
//             fn($q) => $q->where('zone_id', $user->zone_id)
//         );
// }
public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
{
    $user = auth()->user();
    $query = parent::getEloquentQuery();

    if ($user->role === 'admin') {
        $branchIds = $user->branches()->pluck('branches.id')->toArray();
        return $query->whereIn('branch_id', $branchIds);
    }

    return $query->where('branch_id', $user->branch_id);
}

}
