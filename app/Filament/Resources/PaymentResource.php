<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Account;
use App\Models\CostCenter;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'المحاسبة';
    protected static ?string $navigationLabel = 'سندات القبض/الصرف';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('direction')
                ->label('النوع')
                ->required()
                ->options([
                    'in' => 'قبض',
                    'out' => 'صرف',
                ]),

            Forms\Components\DatePicker::make('date')
                ->label('التاريخ')
                ->required(),

            Forms\Components\TextInput::make('amount')
                ->label('المبلغ')
                ->numeric()
                ->required(),

            Forms\Components\Select::make('method')
                ->label('طريقة الدفع')
                ->required()
                ->options([
                    'cash' => 'نقدًا',
                    'bank' => 'تحويل بنكي',
                    'card' => 'بطاقة',
                    'wallet' => 'محفظة',
                    'other' => 'أخرى',
                ])
                ->default('cash'),

            Forms\Components\Select::make('account_id')
                ->label('حساب الصندوق/البنك')
                ->required()
                ->options(Account::query()->orderBy('code')->pluck('name', 'id'))
                ->searchable(),

            Forms\Components\Select::make('cost_center_id')
                ->label('مركز التكلفة (اختياري)')
                ->options(CostCenter::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->nullable(),

            Forms\Components\TextInput::make('reference_type')
                ->label('Reference Type (اختياري)')
                ->maxLength(255)
                ->nullable(),

            Forms\Components\TextInput::make('reference_id')
                ->label('Reference ID (اختياري)')
                ->numeric()
                ->nullable(),

            Forms\Components\Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(3)
                ->nullable(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->label('التاريخ')->date()->sortable(),
                Tables\Columns\TextColumn::make('direction')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => $state === 'in' ? 'قبض' : 'صرف')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')->label('المبلغ')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('method')
                    ->label('الطريقة')
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'cash' => 'نقدًا',
                        'bank' => 'تحويل بنكي',
                        'card' => 'بطاقة',
                        'wallet' => 'محفظة',
                        default => 'أخرى',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('account.name')->label('الحساب')->searchable(),
                Tables\Columns\TextColumn::make('costCenter.name')->label('مركز التكلفة')->toggleable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
