<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label('المفتاح')
                    ->required(),

                Forms\Components\TextInput::make('value')
                    ->label('القيمة')
                    ->required(),
                    FileUpload::make('download_file')
                    ->label('رفع ملف التطبيق')
                    ->directory('downloads') // تحديد مجلد الحفظ داخل storage/app/public/downloads
                    ->acceptedFileTypes(['application/vnd.android.package-archive', 'application/octet-stream'])
                    ->maxSize(100000) // الحد الأقصى للحجم بالـ KB
                    ->preserveFilenames(), //
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->label('المفتاح'),
                Tables\Columns\TextColumn::make('value')->label('القيمة'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
    
        return Auth::user()->role === "master";
    
    }
    
}
