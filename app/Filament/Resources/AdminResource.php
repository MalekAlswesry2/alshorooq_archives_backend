<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminResource\Pages;
use App\Filament\Resources\AdminResource\RelationManagers;
use App\Models\Admin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\CheckboxList;
use App\Models\Service;
use Filament\Infolists\Components\RepeatableEntry;
class AdminResource extends Resource
{
    protected static ?string $model = \App\Models\User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = "المشرف";
    protected static ?string $navigationGroup = "المستخدمين والصلاحيات ";
    protected static ?string $navigationLabel = "المشرفين";
    protected static ?string $modelLabel = "مشرف";
    protected static ?string $pluralLabel = "المشرفين";

public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->where('role', 'admin'); // أو ->where('is_admin', true) حسب نظامك
}
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->label('الاسم')
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('role')
                    ->maxLength(255)
                    ->default('admin')
                    ->readOnly()
                    ->label('النوع'),

                TextInput::make('email')
                    ->email()
                    ->unique()
                    ->required()
                    ->maxLength(255)
                    ->label('البريد الالكتروني')
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),

                // TextInput::make('password')
                //     ->password()
                //     ->required()
                //     ->maxLength(255)
                //     ->label('كلمة المرور')
                //     ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),

                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])->label("الحالة")
                    ->default('active')
                    ->required()
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),

                Select::make('branches')
                    ->label('الفروع')
                    ->multiple()
                    ->required()
                    ->preload()
                    ->reactive()
                    ->relationship('branches', 'name')
                    ->afterStateUpdated(function (callable $set) {
                        $set('zone_id', null);
                    }),


                Select::make('departments')
                    ->multiple()
                    ->preload()
                    ->required()
                    ->relationship('departments', 'name')
                    ->searchable()
                    ->label('الأقسام'),

                Select::make('role_id')
                    ->label('الدور')
                    ->relationship('role', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                CheckboxList::make('services')
                ->relationship('services', 'name')
                ->label('الخدمات المسموح بها')
                ->columns(2)
                ->helperText('حدد الخدمات التي يستطيع هذا المستخدم الوصول إليها'),
                ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label("الاسم")->searchable()->alignCenter(),
                TextColumn::make('email')->label("البريد الالكتروني")->searchable(),
                TextColumn::make('phone')->label("رقم الهاتف")->searchable(),
                TextColumn::make('status')->label("الحالة"),
                TextColumn ::make('services.name')
                ->label('الخدمات')
                ->limit(3),
            ])
            ->filters([
                SelectFilter::make('branch')->relationship('branch', 'name')->label("الفرع"),
                SelectFilter::make('department')->relationship('department', 'name')->label("القسم"),
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
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('بيانات المستخدم')->schema([
                    TextEntry::make('name')->label("الاسم"),
                    TextEntry::make('phone')->label("رقم الهاتف"),
                    TextEntry::make('branch.name')->label("الفرع"),
                    TextEntry::make('department.name')->label("القسم"),
                    TextEntry::make('address')->label("العنوان"),
                ])->columns(3),
                Section::make('حالة المستخدم')->schema([
                    TextEntry::make('role')->label("نوع المستخدم"),
                    TextEntry::make('status')->label("الحالة"),
                ])->columns(2),
                Section::make('الخدمات المرتبطة')
    ->schema([
        RepeatableEntry::make('services')
            ->label('الخدمات')
            ->schema([
                TextEntry::make('name')
                    ->label('اسم الخدمة'),
            ])
            // ->emptyState('لا توجد خدمات مرتبطة'),
    ])
            ]);
    }
        public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('users_view');
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
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }
}
