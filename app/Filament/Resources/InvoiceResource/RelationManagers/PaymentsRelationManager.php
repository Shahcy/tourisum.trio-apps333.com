<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Account;
use App\Models\CostCenter;
use Filament\Forms\Components\Select;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'الدفعات';


    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('date')
                ->label('تاريخ الدفع')
                ->default(now())
                ->required(),

            Forms\Components\TextInput::make('amount')
                ->label('المبلغ')
                ->numeric()
                ->required(),

            Forms\Components\Select::make('method')
                ->label('طريقة الدفع')
                ->options([
                    'cash' => 'Cash',
                    'card' => 'Card',
                    'bank' => 'Bank Transfer',
                    'wallet' => 'Wallet',
                    'other' => 'Other',
                ])
                ->required()
                ->default('cash'),

            Forms\Components\Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(3),

            Forms\Components\Select::make('direction')
                ->label('الاتجاه')
                ->options([
                    'in' => 'قبض',
                    'out' => 'صرف',
                ])
                ->default('in')
                ->required(),

            Select::make('account_id')
                ->label('الحساب')
                ->options(
                    fn() => Account::query()
                        ->where('tenant_id', Filament::getTenant()?->getKey())
                        ->pluck('name', 'id')
                        ->toArray()
                )
                ->searchable()
                ->required(),

            Select::make('cost_center_id')
                ->label('مركز التكلفة')
                ->options(
                    fn() => CostCenter::query()
                        ->where('tenant_id', Filament::getTenant()?->getKey())
                        ->pluck('name', 'id')
                        ->toArray()
                )
                ->searchable()
                ->nullable(),


        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, $livewire) {
                        $data['tenant_id'] = Filament::getTenant()?->getKey();
                        $data['direction'] = $data['direction'] ?? 'in';

                        $invoice = $livewire->getOwnerRecord();

                        return $invoice->payments()->create($data);
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('date')->label('التاريخ')->date(),
                Tables\Columns\TextColumn::make('amount')->label('المبلغ')->money('USD'),
                Tables\Columns\TextColumn::make('method')->label('الطريقة'),
                Tables\Columns\TextColumn::make('direction')->label('الاتجاه'),
                Tables\Columns\TextColumn::make('account.name')->label('الحساب'),
                Tables\Columns\TextColumn::make('notes')->label('ملاحظات')->limit(30),
            ]);
    }
}
