<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobTitleResource\Pages;
use App\Models\JobTitle;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class JobTitleResource extends Resource
{
    protected static ?string $model = JobTitle::class;
    protected static ?string $tenantOwnershipRelationshipName = 'tenant';
    protected static ?string $tenantRelationshipName = 'jobTitles';

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'الإدارة';
    protected static ?string $navigationLabel = 'المسميات الوظيفية';
    protected static ?string $modelLabel = 'مسمى وظيفي';
    protected static ?string $pluralModelLabel = 'المسميات الوظيفية';
    protected static ?int $navigationSort = 21;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('اسم المسمى')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('code')
                ->label('الرمز')
                ->maxLength(50),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('اسم المسمى')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('code')->label('الرمز')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الإنشاء')->dateTime()->sortable(),
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
