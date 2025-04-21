<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceiptResource\Pages;
use App\Filament\Resources\ReceiptResource\RelationManagers;
use App\Models\Receipt;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?int $navigationSort = 2;
    protected static ?string $label = "ايصال";
    protected static ?string $navigationLabel = "الايصالات";
    protected static ?string $modelLabel = "ايصال";
    protected static ?string $pluralLabel = "الايصالات";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('بيانات الايصال')
                ->schema([
                    TextEntry::make('custom_id')->label("رقم الايصال"),
                    TextEntry::make('user.name')
                    ->label("المستخدم"),
                    TextEntry::make('amount')
                    ->formatStateUsing(fn ($state) => number_format($state, 0) . ' د.ل')

                    ->label("القيمة"),
                    TextEntry::make('reference_number')
                    ->label("الرقم الاشاري"),
                    TextEntry::make('bank.name')
                    ->label("المصرف"),

                    TextEntry::make('payment_method')
                    ->label("طريقة الدفع"),

                    TextEntry::make('check_number')
                    ->label("رقم الشيك"),
                    TextEntry::make('system_receipt_number')
                    ->label("رقم الايصال في النظام"),

                ])->columns(3),
                Section::make('صورة الايصال')
                ->schema([
                    ImageEntry::make('image')
                    ->label(""),
                    
                ])->columns(2)

            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('custom_id')
                ->label("رقم الايصال")
                ->sortable(),
                TextColumn::make('user.name')
                ->label("المستخدم")
                ->sortable(),
                TextColumn::make('reference_number')
                ->label("الرقم الاشاري"),
                TextColumn::make('amount')
                ->formatStateUsing(fn ($state) => number_format($state, 0) . ' د.ل')
                ->label("القيمة")->summarize(Sum::make()->label('الاجمالي')->formatStateUsing(fn ($state) => number_format($state, 0) . ' د.ل')),
                TextColumn::make('bank.name')
                ->label("المصرف"),
                
                TextColumn::make('branch.name')
                ->label("الفرع"),
                // TextColumn::make('payment_method')
                // ->label("طريقة الدفع"),
                // TextColumn::make('check_number')
                // ->label("رقم الشيك"),
                // TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state){
                //     'not_received' => 'warning',
                //     'received' => 'success',
                // })->label("الحالة"),
                // ImageColumn::make('image')->square()->visibility('private')
                // ->openUrlInNewTab()
                // ->label("الصورة")
                // ImageColumn::make('image')->defaultImageUrl(url('/images/placeholder.png')),


            ])
            ->filters([
                
                SelectFilter::make('department')->relationship('department', 'name')
                ->label("القسم"),
            
                SelectFilter::make('branch')->relationship('branch', 'name')
                ->label("الفرع"),

                SelectFilter::make('user')->relationship('user', 'name')
                ->label("المستخدم"),

                SelectFilter::make('status')
                ->label("الحالة")
                ->options(
                [
                    'received'=>'مستلمة',
                    'not_received'=>'غير مستلمة'
                ]
                )->attribute('status'),
                SelectFilter::make('payment_method')
                ->label("طريقة الدفع")
                ->options(
                [
                    'transfer'=>'تحويل',
                    'check'=>'شيك',
                    'cash'=>'كاش'
                ]
                )->attribute('payment_method'),
                Filter::make('created_at')
                ->form([
                    DatePicker::make('created_from')->label("التاريخ من"),
                    DatePicker::make('created_until')->label("التاريخ الى"),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\Action::make('pdf') 
                //     ->label('PDF')
                //     ->color('success')
                //     ->icon('heroicon-o-newspaper')
                //     ->action(function (Receipt $receipt) {
                //         return response()->streamDownload(function () use ($receipt) {
                //             echo Pdf::loadHtml(
                //                 Blade::render('receipt', ['receipt' => $receipt])
                //             )->stream();
                //         }, $receipt->custom_id . '.pdf');
                //     }), 

                Tables\Actions\Action::make('pdf')
                ->label('PDF')
                ->color('success')
                ->icon('heroicon-o-newspaper')
                ->action(function (Receipt $receipt) {
                    return response()->stream(function () use ($receipt) {
                        echo Pdf::loadHtml(
                            Blade::render('receipt', ['receipt' => $receipt])
                        )->stream();
                    }, 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="' . $receipt->custom_id . '.pdf"',
                    ]);
                }),
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
            'index' => Pages\ListReceipts::route('/'),
            'create' => Pages\CreateReceipt::route('/create'),
            'view' => Pages\ViewReceipt::route('/{record}'),
            'edit' => Pages\EditReceipt::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('receipts_view');
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();
    
        // المستخدم العادي يرى فقط إيصالاته
        if ($user->role === 'user') {
            return $query->where('user_id', $user->id);
        }
    
        // المسؤول يرى حسب الفروع والأقسام المربوطة به
        if ($user->role === 'admin') {
            $branchIds = $user->branches()->pluck('branches.id')->toArray();
            $departmentIds = $user->departments()->pluck('departments.id')->toArray();
    
            return $query
                ->when(!empty($branchIds), fn($q) => $q->whereIn('branch_id', $branchIds))
                ->when(!empty($departmentIds), fn($q) => $q->whereIn('department_id', $departmentIds));
        }
    
        // أي دور آخر (مثلاً master) يرى كل شيء
        return $query;
    }
    

}
