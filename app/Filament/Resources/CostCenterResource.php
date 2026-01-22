<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CostCenterResource\Pages;
use App\Models\CostCenter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Concerns\ScopesToTenant;


class CostCenterResource extends Resource
{
    use ScopesToTenant;
    protected static ?string $model = CostCenter::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?int $navigationSort = 41;

    /**
     * Avoid hardcoded strings so locale switching works properly.
     */
    public static function getNavigationGroup(): ?string
    {
        return __('accounting.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('accounting.cost_centers.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('accounting.cost_centers.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('accounting.cost_centers.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->label(__('accounting.cost_centers.fields.type'))
                ->required()
                ->options([
                    'department' => __('accounting.cost_centers.types.department'),
                    'employee'   => __('accounting.cost_centers.types.employee'),
                ]),

            Forms\Components\TextInput::make('name')
                ->label(__('accounting.cost_centers.fields.name'))
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('department_id')
                ->label(__('accounting.cost_centers.fields.department_id'))
                ->numeric()
                ->nullable(),

            Forms\Components\TextInput::make('employee_id')
                ->label(__('accounting.cost_centers.fields.employee_id'))
                ->numeric()
                ->nullable(),

            Forms\Components\Toggle::make('is_active')
                ->label(__('accounting.cost_centers.fields.is_active'))
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label(__('accounting.cost_centers.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'department' => __('accounting.cost_centers.types.department'),
                        'employee'   => __('accounting.cost_centers.types.employee'),
                        default      => (string) $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('accounting.cost_centers.fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('department_id')
                    ->label(__('accounting.cost_centers.fields.department_id_short'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('employee_id')
                    ->label(__('accounting.cost_centers.fields.employee_id_short'))
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('accounting.cost_centers.fields.is_active'))
                    ->boolean(),
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
