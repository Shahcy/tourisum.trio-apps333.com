<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CostCenterResource\Pages;
use App\Models\CostCenter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CostCenterResource extends Resource
{
    protected static ?string $model = CostCenter::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'المحاسبة';
    protected static ?string $navigationLabel = 'مراكز التكلفة';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->label('النوع')
                ->required()
                ->options([
                    'department' => 'قسم',
                    'employee' => 'موظف',
                ]),

            Forms\Components\TextInput::make('name')
                ->label('الاسم')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('department_id')
                ->label('Department ID (اختياري)')
                ->numeric()
                ->nullable(),

            Forms\Components\TextInput::make('employee_id')
                ->label('Employee ID (اختياري)')
                ->numeric()
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
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => $state === 'department' ? 'قسم' : 'موظف')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('department_id')->label('Department ID')->toggleable(),
                Tables\Columns\TextColumn::make('employee_id')->label('Employee ID')->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->label('مفعّل')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCostCenters::route('/'),
            'create' => Pages\CreateCostCenter::route('/create'),
            'edit' => Pages\EditCostCenter::route('/{record}/edit'),
        ];
    }
}
