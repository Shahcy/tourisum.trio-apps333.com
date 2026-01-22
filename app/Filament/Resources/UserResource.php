<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\TranslatesResourceAttributes;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    use TranslatesResourceAttributes;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'saas.group';
    protected static ?string $navigationLabel = 'Users';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users';
    protected static ?int $navigationSort = 97;

    protected static function isSuperAdmin(): bool
    {
        return (bool) Filament::auth()->user()?->hasRole('super_admin');
    }

    protected static function isPortalPanel(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'portal';
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Filament::auth()->user();

        return (bool) ($user?->hasAnyRole(['super_admin', 'admin', 'company_admin']));
    }

    public static function canCreate(): bool
    {
        $user = Filament::auth()->user();
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'admin', 'company_admin']);
    }

    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();

        return (bool) ($user?->hasAnyRole(['super_admin', 'admin', 'company_admin']));
    }

    public static function canEdit($record): bool
    {
        $user = Filament::auth()->user();

        return (bool) ($user?->hasAnyRole(['super_admin', 'admin', 'company_admin']));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('User Information')
                ->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Select::make('tenant_id')
                        ->label('Company (Tenant)')
                        ->relationship('tenant', 'name')
                        ->searchable()
                        ->preload()
                        ->required(fn(): bool => static::isSuperAdmin())
                        ->visible(fn(): bool => static::isSuperAdmin() && ! static::isPortalPanel())
                        ->disabled(fn(): bool => static::isPortalPanel()),

                    Select::make('roles')
                        ->label('Roles')
                        ->multiple()
                        ->relationship(
                            name: 'roles',
                            titleAttribute: 'name',
                            modifyQueryUsing: function (Builder $query): void {
                                $query->where('guard_name', 'web');

                                if (static::isSuperAdmin()) {
                                    $query->whereIn('name', ['super_admin', 'admin', 'company_admin']);
                                    return;
                                }

                                $query->whereNotIn('name', ['super_admin', 'admin', 'company_admin']);
                            }
                        )
                        ->preload()
                        ->searchable(),
                ])
                ->columns(2),

            Section::make('Password')
                ->schema([
                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->minLength(8)
                        ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn(?string $state): bool => filled($state))
                        ->required(fn(string $context): bool => $context === 'create'),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tenant.name')
                    ->label('Company')
                    ->sortable()
                    ->searchable()
                    ->visible(fn(): bool => static::isSuperAdmin()),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(', ')
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if (! static::isSuperAdmin()) {
            $data['tenant_id'] = Filament::getTenant()?->id ?? $user?->tenant_id;
        }

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $user = Auth::user();

        if (! static::isSuperAdmin()) {
            $data['tenant_id'] = Filament::getTenant()?->id ?? $user?->tenant_id;
        }

        return $data;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (static::isSuperAdmin()) {
            return $query->whereHas('roles', function (Builder $rolesQuery): void {
                $rolesQuery->whereIn('name', ['super_admin', 'admin', 'company_admin']);
            });
        }

        $user = Auth::user();
        $tenantId = Filament::getTenant()?->id ?? $user?->tenant_id;

        return $query
            ->where('tenant_id', $tenantId)
            ->whereDoesntHave('roles', function (Builder $rolesQuery): void {
                $rolesQuery->where('name', 'super_admin');
            });
    }
}
