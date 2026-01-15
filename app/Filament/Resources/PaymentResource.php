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

    /**
     * Avoid hardcoded strings so locale switching works properly.
     */
    public static function getNavigationGroup(): ?string
    {
        return __('finance.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('finance.payments.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('finance.payments.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('finance.payments.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('direction')
                ->label(__('finance.payments.fields.direction'))
                ->required()
                ->options([
                    'in'  => __('finance.payments.directions.in'),
                    'out' => __('finance.payments.directions.out'),
                ]),

            Forms\Components\DatePicker::make('date')
                ->label(__('finance.payments.fields.date'))
                ->required(),

            Forms\Components\TextInput::make('amount')
                ->label(__('finance.payments.fields.amount'))
                ->numeric()
                ->required(),

            Forms\Components\Select::make('method')
                ->label(__('finance.payments.fields.method'))
                ->required()
                ->options([
                    'cash'   => __('finance.payments.methods.cash'),
                    'bank'   => __('finance.payments.methods.bank'),
                    'card'   => __('finance.payments.methods.card'),
                    'wallet' => __('finance.payments.methods.wallet'),
                    'other'  => __('finance.payments.methods.other'),
                ])
                ->default('cash'),

            Forms\Components\Select::make('account_id')
                ->label(__('finance.payments.fields.account'))
                ->required()
                ->options(Account::query()->orderBy('code')->pluck('name', 'id'))
                ->searchable(),

            Forms\Components\Select::make('cost_center_id')
                ->label(__('finance.payments.fields.cost_center'))
                ->options(CostCenter::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->nullable(),

            Forms\Components\TextInput::make('reference_type')
                ->label(__('finance.payments.fields.reference_type'))
                ->maxLength(255)
                ->nullable(),

            Forms\Components\TextInput::make('reference_id')
                ->label(__('finance.payments.fields.reference_id'))
                ->numeric()
                ->nullable(),

            Forms\Components\Textarea::make('notes')
                ->label(__('finance.payments.fields.notes'))
                ->rows(3)
                ->nullable(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label(__('finance.payments.fields.date'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('direction')
                    ->label(__('finance.payments.fields.direction'))
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'in'  => __('finance.payments.directions.in'),
                        'out' => __('finance.payments.directions.out'),
                        default => (string) $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('finance.payments.fields.amount'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('method')
                    ->label(__('finance.payments.fields.method'))
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'cash'   => __('finance.payments.methods.cash'),
                        'bank'   => __('finance.payments.methods.bank'),
                        'card'   => __('finance.payments.methods.card'),
                        'wallet' => __('finance.payments.methods.wallet'),
                        'other'  => __('finance.payments.methods.other'),
                        default  => (string) $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('account.name')
                    ->label(__('finance.payments.fields.account'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('costCenter.name')
                    ->label(__('finance.payments.fields.cost_center_short'))
                    ->toggleable(),
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
