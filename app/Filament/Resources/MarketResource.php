<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketResource\Pages;
use App\Filament\Resources\MarketResource\RelationManagers;
use App\Models\Area;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Market;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;

class MarketResource extends Resource
{
    protected static ?string $model = Market::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?int $navigationSort = 3;
    protected static ?string $label = "عميل";
    protected static ?string $navigationLabel = "العملاء";
    protected static ?string $modelLabel = "عميل";
    protected static ?string $pluralLabel = "العملاء";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->label("اسم العميل")
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                ->label("رقم هاتف العميل")
                    ->tel()
                    ->required()
                    ->maxLength(255),
                    Select::make('area_id')
                    ->label('المنطقة')
                    ->required()
                    ->options(Area::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\TextInput::make('address')
                ->label("عنوان العميل")
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('system_market_number')
                ->label("رقم العميل في المنظومة")
                    ->required()
                    ->maxLength(255),

                    
                    Select::make('department_id')
                    ->label('القسم')
                    ->required()
                    ->options(Department::all()->pluck('name', 'id'))
                    ->searchable(),

                    Select::make('branch_id')
                    ->label('الفرع')
                    ->required()
                    ->options(Branch::all()->pluck('name', 'id'))
                    ->searchable(),
                // Forms\Components\TextInput::make('role')
                //     ->required()
                //     ->maxLength(255)
                //     ->default('user'),

                Select::make('user_id')
                ->label('المستخدم')
                ->required()
                ->options(User::all()->pluck('name', 'id'))
                ->searchable(),

                    Forms\Components\TextInput::make('status')
                    ->label("الحالة")
                    ->required()
                    ->maxLength(255)
                    ->default('active'),
                    Forms\Components\TextInput::make('role')
                    ->label("الدور")
                    ->required()
                    ->maxLength(255)
                    ->default('admin'),
            ]);
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('بيانات العميل')
                ->schema([
                    // TextEntry::make('custom_id')->label("رقم الايصال"),
                    TextEntry::make('name')
                    ->label("اسم العميل"),
                    TextEntry::make('phone')
                    ->label("رقم الهاتف"),
                    TextEntry::make('system_market_number')
                    ->label("رقم المنظومة"),
 
                    TextEntry::make('address')
                    ->label("العنوان"),

                ])->columns(3),
                Section::make('بيانات اضافية')
                ->schema([
                    TextEntry::make('branch.name')
                    ->label("الفرع"),

                    TextEntry::make('department.name')
                    ->label("القسم"),

                    TextEntry::make('area.name')
                    ->label("خط السير"),

                    
                    TextEntry::make('status')
                    ->label("الحالة"),
                ])->columns(2)

            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                ->label('الاسم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                ->label('رقم الهاتف')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                ->label('العنوان')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                ->label('الحالة')

                    ->searchable(),
                Tables\Columns\TextColumn::make('system_market_number')
                ->label('رقم المنظومة')

                    ->searchable(),
                Tables\Columns\TextColumn::make('branch.name')
                ->label('الفرع')
                ->searchable(),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch')->relationship('branch', 'name')->label("الفرع"),
                SelectFilter::make('department')->relationship('department', 'name')->label("القسم"),
                SelectFilter::make('user')->relationship('user', 'name')->label("المستخدم"),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarkets::route('/'),
            'create' => Pages\CreateMarket::route('/create'),
            'view' => Pages\ViewMarket::route('/{record}'),
            'edit' => Pages\EditMarket::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('markets_view');
    }
    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission('markets_create');
    }
    public static function canUpdate(): bool
    {
        return auth()->user()->hasPermission('markets_update');
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();
    
        if ($user->role === 'admin') {
            $branchIds = $user->branches()->pluck('branches.id')->toArray();
            $departmentIds = $user->departments()->pluck('departments.id')->toArray();
    
            return $query
                ->when(!empty($branchIds), fn($q) => $q->whereIn('branch_id', $branchIds))
                ->when(!empty($departmentIds), fn($q) => $q->whereIn('department_id', $departmentIds));
        }
    
        // للمندوب نعرض الأسواق الخاصة به فقط
        return $query->where('user_id', $user->id);
    }
    
    
}
