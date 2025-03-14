<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 6;
    protected static ?string $label = "قسم";
    protected static ?string $navigationLabel = "الاقسام";
    protected static ?string $modelLabel = "قسم";
    protected static ?string $pluralLabel = "الاقسام";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label("اسم القسم")
                ->required()
                ->maxLength(255),
    
            Select::make('status')->label("الحالة")
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ])
                ->default('active')
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable()->label("اسم القسم"),
                TextColumn::make('status')->label("الحالة")->badge()->color(fn (string $state): string => match ($state){
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                })->colors([
                    'success' => 'active',
                    'danger' => 'inactive',
                ]),

                    
            ])
            ->filters([
                SelectFilter::make('status')->label("الحالة")
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),

                    // SelectFilter::make('branch')->relationship('branch', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('departments_view');
    }    

}
