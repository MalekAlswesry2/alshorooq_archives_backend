<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = "المستخدم";
    protected static ?string $navigationLabel = "المستخدمين";
    protected static ?string $modelLabel = "مستخدم";
    protected static ?string $pluralLabel = "المستخدمين";

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
                    ->label('الدور'),

                TextInput::make('email')
                    ->email()
                    ->unique()
                    ->required()
                    ->maxLength(255)
                    ->label('البريد الالكتروني')
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),

                TextInput::make('password')
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

                Select::make('zone_id')
                    ->label('خط السير')
                    ->options(function (callable $get) {
                        $branchIds = $get('branches');
                        return is_array($branchIds) && !empty($branchIds)
                            ? \App\Models\Zone::whereIn('branch_id', $branchIds)->pluck('name', 'id')
                            : [];
                    })
                    ->searchable()
                    ->preload()
                    ->reactive(),

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
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label("الاسم")->searchable()->alignCenter(),
                TextColumn::make('email')->label("البريد الالكتروني")->searchable(),
                TextColumn::make('phone')->label("رقم الهاتف")->searchable(),
                TextColumn::make('branch.name')->label("الفرع")->searchable(),
                TextColumn::make('department.name')->label("القسم")->searchable(),
                TextColumn::make('address')->label("العنوان")->searchable(),
                TextColumn::make('status')->label("الحالة"),
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
                Tables\Actions\BulkActionGroup::make([]),
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
            ]);
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

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();

        if ($user->role === 'master') {
            return parent::getEloquentQuery()->where('role', '!=', 'master');
        }

        $query = parent::getEloquentQuery()->where('role', '!=', 'master');

        $branchIds = $user->branches()->pluck('branches.id')->toArray();
        $departmentIds = $user->departments()->pluck('departments.id')->toArray();

        if (empty($branchIds)) {
            $branchIds[] = $user->branch_id;
        }
        if (empty($departmentIds)) {
            $departmentIds[] = $user->department_id;
        }

        return $query->where(function ($q) use ($branchIds, $departmentIds) {
            $q->whereHas('branches', function ($q2) use ($branchIds) {
                $q2->whereIn('branches.id', $branchIds);
            })
            ->orWhereHas('departments', function ($q3) use ($departmentIds) {
                $q3->whereIn('departments.id', $departmentIds);
            })
            ->orWhere(function ($q4) use ($branchIds, $departmentIds) {
                $q4->whereIn('branch_id', $branchIds)
                   ->whereIn('department_id', $departmentIds);
            });
        });
    }
}
