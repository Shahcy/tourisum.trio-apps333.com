<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobTitleResource\Pages;
use App\Models\JobTitle;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\Concerns\ScopesToTenant;

class JobTitleResource extends Resource
{
    use ScopesToTenant;
    protected static ?string $model = JobTitle::class;

    // Tenancy
    protected static ?string $tenantOwnershipRelationshipName = 'tenant';
    protected static ?string $tenantRelationshipName = 'jobTitles';

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?int $navigationSort = 21;

    /**
     * Avoid hardcoded strings so locale switching works properly.
     */
    public static function getNavigationGroup(): ?string
    {
        return __('management.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('management.job_titles.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('management.job_titles.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('management.job_titles.plural');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label(__('management.job_titles.fields.name'))
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('code')
                ->label(__('management.job_titles.fields.code'))
                ->maxLength(50),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('management.job_titles.fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label(__('management.job_titles.fields.code'))
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
            'index' => Pages\ListJobTitles::route('/'),
            'create' => Pages\CreateJobTitle::route('/create'),
            'edit' => Pages\EditJobTitle::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) (
            $user?->can('view_any_job::title')
            || $user?->hasAnyRole(['admin', 'super_admin'])
        );
    }
}
