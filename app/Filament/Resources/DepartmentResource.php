<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    // Tenancy
    protected static ?string $tenantOwnershipRelationshipName = 'tenant';
    protected static ?string $tenantRelationshipName = 'departments';

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?int $navigationSort = 20;

    /**
     * Avoid hardcoded strings so locale switching works properly.
     */
    public static function getNavigationGroup(): ?string
    {
        return __('management.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('management.departments.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('management.departments.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('management.departments.plural');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label(__('management.departments.fields.name'))
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('code')
                ->label(__('management.departments.fields.code'))
                ->maxLength(50),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('management.departments.fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label(__('management.departments.fields.code'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) (
            $user?->can('view_any_department')
            || $user?->hasAnyRole(['admin', 'super_admin'])
        );
    }
}
