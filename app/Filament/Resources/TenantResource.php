<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\Tenant;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'SaaS';
    protected static ?string $navigationLabel = 'Companies';
    protected static ?string $modelLabel = 'Company';
    protected static ?string $pluralModelLabel = 'Companies';
    protected static bool $shouldRegisterNavigation = true;


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Section::make('Company Information')
                ->schema([
                    TextInput::make('name')
                        ->label('Company name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('domain')
                        ->label('Domain (optional)')
                        ->helperText('Example: company.example.com')
                        ->maxLength(255),

                    FileUpload::make('logo_path')
                        ->label('Logo (optional)')
                        ->directory('tenants/logos')
                        ->image()
                        ->imageEditor()
                        ->maxSize(2048),

                    ColorPicker::make('primary_color')
                        ->label('Primary color (optional)'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->circular()
                    ->size(36)
                    ->defaultImageUrl(null),

                TextColumn::make('name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('domain')
                    ->label('Domain')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit'   => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
