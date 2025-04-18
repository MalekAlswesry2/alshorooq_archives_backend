<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Validation\Rule;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 1;
    // protected static ?string $recordTitleAttribute = 'Users';
    // protected static ?string $modelLabel = 'User';
    protected static ?string $label = "المستخدم";
    protected static ?string $navigationLabel = "المستخدمين";
    protected static ?string $modelLabel = "مستخدم";
    protected static ?string $pluralLabel = "المستخدمين";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('الاسم')
                    ->maxLength(255)
                    ,
                    TextInput::make('phone')
                        ->label('رقم الهاتف')
                        ->required()
                        ->unique(ignoreRecord: true),
                    
                    Select::make('branch_id')
                    ->label('الفرع')
                    ->options(Branch::all()->pluck('name', 'id'))
                    ->searchable(),
                    
                    Select::make('department_id')
                    ->label('القسم')
                    ->options(Department::all()->pluck('name', 'id'))
                    ->searchable(),


                    Select::make('zone_id')
                    ->label('خط السير')
                    ->options(Zone::all()->pluck('name', 'id'))
                    ->searchable()
                    ,


                    // Forms\Components\Select::make('department')->required()->options([
                    //     'all' => 'All',
                    //     'alshoroq' => 'الشروق',
                    //     'omalk' => 'العملاق',
                    // ]),
                // Forms\Components\TextInput::make('address')
                //     ->maxLength(255),

                Forms\Components\TextInput::make('role')
                    ->maxLength(255)
                    ->default('admin')
                    ->readOnly()
                    ->label('الدور'),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->unique()
                    ->required()
                    ->maxLength(255)
                    ->label('البريد الالكتروني')
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),
     
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->label('كلمة المرور')
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),

                    Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])->label("")
                    ->default('active')
                    ->required()
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),

                    Select::make('permissions')
                    ->relationship('permissions', 'name')
                    ->multiple()
                    ->preload()
                    ->label('الصلاحيات')
                    ->searchable(),

                    Select::make('branches')
                    ->multiple()
                    ->preload()
                    ->relationship('branches', 'name') // مهم جدًا للربط التلقائي
                    ->searchable()
                    ->label('الفروع'),

                    Select::make('departments')
                    ->multiple()
                    ->preload()
                    ->relationship('departments', 'name') // مهم جدًا للربط التلقائي
                    ->searchable()
                    ->label('الأقسام'),



                
            ]);

    }




    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->label("الاسم")
                    ->searchable()
                    ->alignCenter(),
                                    Tables\Columns\TextColumn::make('email')
                ->label("البريد الالكتروني")

                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                ->label("رقم الهاتف")

                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('branch.name')
                ->label("الفرع")

                    ->searchable(),
                Tables\Columns\TextColumn::make('department.name')
                ->label("القسم")
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                ->label("العنوان")

                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                ->label("الحالة"),

                // Tables\Columns\TextColumn::make('role')
                // ->label("الاسم")
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('balance')
                //     ->numeric()
                //     ->sortable(),


                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
  
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
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('بيانات المستخدم')
                ->schema([
                    TextEntry::make('name')->label("الاسم"),
                    TextEntry::make('phone')->label("رقم الهاتف"),
                    TextEntry::make('branch.name')->label("الفرع"),
                    TextEntry::make('department.name')->label("القسم"),
                    TextEntry::make('address')->label("العنوان"),

                ])->columns(3),
                Section::make('حالة المستخدم')
                ->schema([

                    // TextEntry::make('branch.name'),
                    // TextEntry::make('department.name'),
                    TextEntry::make('role')->label("نوع المستخدم"),
                    TextEntry::make('status')->label("الحالة"),
                ])->columns(2)

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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
        
    }
    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('users_view');
    }
}
