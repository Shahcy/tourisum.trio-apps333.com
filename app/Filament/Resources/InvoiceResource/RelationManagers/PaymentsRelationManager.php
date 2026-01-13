<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\InvoicePayment;
use App\Models\Account;
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
                ])
                ->required()
                ->default('cash'),

            Forms\Components\TextInput::make('reference')
                ->label('Reference')
                ->maxLength(255),

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
                // ...
            ]);
    }
}
