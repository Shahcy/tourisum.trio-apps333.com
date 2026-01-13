<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'المحاسبة';
    protected static ?string $navigationLabel = 'شجرة الحسابات';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->label('الكود')
                ->required()
                ->maxLength(50),

            Forms\Components\TextInput::make('name')
                ->label('اسم الحساب')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('type')
                ->label('النوع')
                ->required()
                ->options([
                    'asset' => 'أصول',
                    'liability' => 'خصوم',
                    'equity' => 'حقوق ملكية',
                    'revenue' => 'إيرادات',
                    'expense' => 'مصروفات',
                ]),

            Forms\Components\Select::make('parent_id')
                ->label('الحساب الأب')
                ->relationship('parent', 'name')
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\Toggle::make('is_active')
                ->label('مفعّل')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('الكود')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'asset' => 'أصول',
                        'liability' => 'خصوم',
                        'equity' => 'حقوق ملكية',
                        'revenue' => 'إيرادات',
                        'expense' => 'مصروفات',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('مفعّل')->boolean(),
                Tables\Columns\TextColumn::make('parent.name')->label('الأب')->toggleable(),
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
