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

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'الفواتير';

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();
        return parent::getEloquentQuery()->where('tenant_id', $user->tenant_id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('customer_id')
                ->label('العميل')
                ->required()
                ->options(
                    Customer::where('tenant_id', Filament::auth()->user()->tenant_id)
                        ->pluck('full_name', 'id')
                ),

            Forms\Components\Repeater::make('items')
                ->relationship()
                ->schema([
                    Forms\Components\TextInput::make('title')->required(),
                    Forms\Components\TextInput::make('qty')->numeric()->default(1),
                    Forms\Components\TextInput::make('unit_price')->numeric()->default(0),
                ])
                ->columns(3),

            Forms\Components\Textarea::make('notes'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('number'),
            Tables\Columns\TextColumn::make('customer.full_name'),
            Tables\Columns\TextColumn::make('total')->money('USD'),
            Tables\Columns\TextColumn::make('paid_amount')->money('USD'),
            Tables\Columns\TextColumn::make('remaining')->money('USD'),
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
