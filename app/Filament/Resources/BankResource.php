<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankResource\Pages;
use App\Filament\Resources\BankResource\RelationManagers;
use App\Models\Bank;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankResource extends Resource
{
    protected static ?string $model = Bank::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?int $navigationSort = 4;
    protected static ?string $label = "مصرف";
    protected static ?string $navigationLabel = "المصارف";
    protected static ?string $modelLabel = "مصرف";
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                TextInput::make('name')->label("اسم المصرف"),
                TextInput::make('account_number')->label("رقم الحساب"),
                // TextInput::make('branch'),
                Select::make('branch_id')
                ->label('الفرع')
                ->required()
                ->options(Branch::all()->pluck('name', 'id'))
                // ->searchable(),
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('name')->label("اسم المصرف"),
            TextColumn::make('account_number')->label("رقم الحساب"),
        ])
            ->filters([
                SelectFilter::make('branch')->relationship('branch', 'name')->label("الفرع"),
                SelectFilter::make('department')->relationship('department', 'name')->label("القسم"),
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
            'index' => Pages\ListBanks::route('/'),
            'create' => Pages\CreateBank::route('/create'),
            'view' => Pages\ViewBank::route('/{record}'),
            'edit' => Pages\EditBank::route('/{record}/edit'),
        ];
    }
}
