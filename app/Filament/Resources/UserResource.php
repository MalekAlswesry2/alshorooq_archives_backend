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
                    
                    // Select::make('branch_id')
                    // ->label('الفرع')
                    // ->reactive() // مهم لتحديث القيم المرتبطة

                    // ->options(Branch::all()->pluck('name', 'id'))
                    // ->searchable(),
                    
                    // Select::make('department_id')
                    // ->label('القسم')
                    // ->options(Department::all()->pluck('name', 'id'))
                    // ->searchable(),


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
                    Select::make('branches')
                    ->label('الفروع')
                    ->multiple()
                    ->required()
                    ->preload()
                    ->reactive()
                    ->relationship('branches', 'name')
                    ->afterStateUpdated(function (callable $set) {
                        $set('zone_id', null); // إعادة تعيين خط السير عند تغيير الفروع
                    }),
                
                Select::make('zone_id')
                    ->label('خط السير')
                    ->options(function (callable $get) {
                        $branchIds = $get('branches');
                
                        if (!is_array($branchIds) || empty($branchIds)) {
                            return [];
                        }
                
                        return \App\Models\Zone::whereIn('branch_id', $branchIds)->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->reactive(),
                

                    Select::make('departments')
                    ->multiple()
                    ->preload()
                    ->required()
                    ->relationship('departments', 'name') // مهم جدًا للربط التلقائي
                    ->searchable()
                    ->label('الأقسام'),


                    Select::make('permissions')
                    ->relationship('permissions', 'name')
                    ->multiple()
                    ->preload()
                    ->label('الصلاحيات')
                    ->searchable(),

                    // Select::make('branches')
                    // ->label('الفروع')
                    // ->multiple()
                    // ->preload()
                    // ->reactive()
                    // ->options(\App\Models\Branch::all()->pluck('name', 'id'))
                    // ->searchable(),



                
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
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
{
    $user = auth()->user();

    // ✅ إذا كان Master، يرجع كل المستخدمين بدون فلترة
    if ($user->role === 'master') {
        return parent::getEloquentQuery()->where('role', '!=', 'master');
    }

    $query = parent::getEloquentQuery()->where('role', '!=', 'master');

    // جلب الفروع والأقسام المرتبطة بالمستخدم
    $branchIds = $user->branches()->pluck('branches.id')->toArray();
    $departmentIds = $user->departments()->pluck('departments.id')->toArray();

    // fallback إلى الفرع والقسم الموجودين مباشرة في جدول users (للمستخدمين بدون تخصيص)
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

    // public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    // {
    //     $user = auth()->user();
    
    //     $query = parent::getEloquentQuery()->where('role', '!=', 'master');
    
    //     // جلب الفروع والأقسام المرتبطة بالمستخدم
    //     $branchIds = $user->branches()->pluck('branches.id')->toArray();
    //     $departmentIds = $user->departments()->pluck('departments.id')->toArray();
    
    //     // fallback إلى الفرع والقسم الموجودين مباشرة في جدول users (للمستخدمين بدون تخصيص)
    //     if (empty($branchIds)) {
    //         $branchIds[] = $user->branch_id;
    //     }
    //     if (empty($departmentIds)) {
    //         $departmentIds[] = $user->department_id;
    //     }
    
    //     return $query->where(function ($q) use ($branchIds, $departmentIds) {
    //         $q->whereHas('branches', function ($q2) use ($branchIds) {
    //             $q2->whereIn('branches.id', $branchIds);
    //         })
    //         ->orWhereHas('departments', function ($q3) use ($departmentIds) {
    //             $q3->whereIn('departments.id', $departmentIds);
    //         })
    //         // تشمل المستخدمين الذين لم يتم ربطهم بفروع أو أقسام ولكن لديهم قيم مباشرة
    //         ->orWhere(function ($q4) use ($branchIds, $departmentIds) {
    //             $q4->whereIn('branch_id', $branchIds)
    //                ->whereIn('department_id', $departmentIds);
    //         });
    //     });
    // }
    
    
//     public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
// {
//     $user = auth()->user();

//     $query = parent::getEloquentQuery()->where('role', '!=', 'master');

//     // فقط للإدمن نطبق الفلترة
//     if ($user->role !== 'admin') {
//         return $query;
//     }

//     $branchIds = $user->branches()->pluck('branches.id')->toArray();
//     $departmentIds = $user->departments()->pluck('departments.id')->toArray();

//     return $query
//         ->when(!empty($branchIds), fn($q) => $q->whereIn('branch_id', $branchIds), fn($q) => $q->where('branch_id', $user->branch_id))
//         ->when(!empty($departmentIds), fn($q) => $q->whereIn('department_id', $departmentIds), fn($q) => $q->where('department_id', $user->department_id));
// }

}
