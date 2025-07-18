<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationGroup = "المستخدمين والصلاحيات ";

    protected static ?string $label = "الخدمة";
    protected static ?string $navigationLabel = "الخدمات";
    protected static ?string $modelLabel = "خدمة";
    protected static ?string $pluralLabel = "الخدمات";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                            Forms\Components\TextInput::make('key')
                ->required()
                ->unique(ignoreRecord: true)
                ->label('المفتاح'),

            Forms\Components\TextInput::make('name')
                ->required()
                ->label('اسم الخدمة'),

            Forms\Components\TextInput::make('icon')
                ->label('الأيقونة'),

            Forms\Components\TextInput::make('route')
                ->label('المسار'),

            Forms\Components\Select::make('state')
                ->options([
                    'ready' => 'جاهزة',
                    'coming_soon' => 'قريبًا',
                    'disabled' => 'معطلة',
                ])
                ->label('الحالة'),

            Forms\Components\Toggle::make('is_active')
                ->label('مفعلة')
                ->default(true),
        ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                            Tables\Columns\TextColumn::make('key')->label('المفتاح'),
            Tables\Columns\TextColumn::make('name')->label('الاسم'),
            Tables\Columns\TextColumn::make('icon')->label('الأيقونة'),
            Tables\Columns\TextColumn::make('state')->label('الحالة'),
            Tables\Columns\TextColumn::make('route')->label('المسار'),
            Tables\Columns\IconColumn::make('is_active')
                ->boolean()
                ->label('مفعلة'),
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
        public static function canViewAny(): bool
    {

        return Auth::user()->role === "master";

    }
}
