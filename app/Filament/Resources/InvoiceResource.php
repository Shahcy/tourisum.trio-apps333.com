<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Customer;
use App\Models\Invoice;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Concerns\ScopesToTenant;
use Illuminate\Support\Facades\Auth;

class InvoiceResource extends Resource
{
    use ScopesToTenant;
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    /**
     * Avoid hardcoded strings so locale switching works properly.
     */
    public static function getNavigationGroup(): ?string
    {
        return __('finance.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('finance.invoices.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('finance.invoices.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('finance.invoices.plural');
    }

    public static function form(Form $form): Form
    {
        $tenantId = Filament::getTenant()?->getKey() ?? Auth::user()?->tenant_id;
        return $form->schema([
            Forms\Components\Select::make('customer_id')
                ->label(__('finance.invoices.fields.customer'))
                ->required()
                ->searchable()
                ->preload()
                ->options(
                    Customer::query()
                        ->where('tenant_id', $tenantId)
                        ->orderBy('full_name')
                        ->pluck('full_name', 'id')
                ),

            Forms\Components\Repeater::make('items')
                ->label(__('finance.invoices.fields.items'))
                ->relationship()
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label(__('finance.invoices.items.title'))
                        ->required(),

                    Forms\Components\TextInput::make('qty')
                        ->label(__('finance.invoices.items.qty'))
                        ->numeric()
                        ->default(1),

                    Forms\Components\TextInput::make('unit_price')
                        ->label(__('finance.invoices.items.unit_price'))
                        ->numeric()
                        ->default(0),
                ])
                ->columns(3),

            Forms\Components\Textarea::make('notes')
                ->label(__('finance.invoices.fields.notes')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('number')
                ->label(__('finance.invoices.fields.number')),

            Tables\Columns\TextColumn::make('customer.full_name')
                ->label(__('finance.invoices.fields.customer')),

            Tables\Columns\TextColumn::make('total')
                ->label(__('finance.invoices.fields.total'))
                ->money('USD'),

            Tables\Columns\TextColumn::make('paid_amount')
                ->label(__('finance.invoices.fields.paid_amount'))
                ->money('USD'),

            Tables\Columns\TextColumn::make('remaining')
                ->label(__('finance.invoices.fields.remaining'))
                ->money('USD'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\InvoiceResource\RelationManagers\PaymentsRelationManager::class,
        ];
    }
}
