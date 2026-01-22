<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\ScopesToTenant;

class AccountResource extends Resource
{
    use ScopesToTenant;
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 40;

    /**
     * IMPORTANT:
     * لا تستخدم نصوص ثابتة هنا. استخدم getters حتى تتغير حسب اللغة.
     */
    public static function getNavigationGroup(): ?string
    {
        return __('accounting.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('accounting.accounts.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('accounting.accounts.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('accounting.accounts.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->label(__('accounting.accounts.fields.code'))
                ->required()
                ->maxLength(50),

            Forms\Components\TextInput::make('name')
                ->label(__('accounting.accounts.fields.name'))
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('type')
                ->label(__('accounting.accounts.fields.type'))
                ->required()
                ->options([
                    'asset' => __('accounting.accounts.types.asset'),
                    'liability' => __('accounting.accounts.types.liability'),
                    'equity' => __('accounting.accounts.types.equity'),
                    'revenue' => __('accounting.accounts.types.revenue'),
                    'expense' => __('accounting.accounts.types.expense'),
                ]),

            Forms\Components\Select::make('parent_id')
                ->label(__('accounting.accounts.fields.parent'))
                ->relationship('parent', 'name')
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\Toggle::make('is_active')
                ->label(__('accounting.accounts.fields.is_active'))
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('accounting.accounts.fields.code'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('accounting.accounts.fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('accounting.accounts.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'asset' => __('accounting.accounts.types.asset'),
                        'liability' => __('accounting.accounts.types.liability'),
                        'equity' => __('accounting.accounts.types.equity'),
                        'revenue' => __('accounting.accounts.types.revenue'),
                        'expense' => __('accounting.accounts.types.expense'),
                        default => (string) $state,
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('accounting.accounts.fields.is_active'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('accounting.accounts.fields.parent'))
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('code');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
