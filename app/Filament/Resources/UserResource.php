<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use App\Filament\Resources\Concerns\TranslatesResourceAttributes;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class UserResource extends Resource
{
    use TranslatesResourceAttributes;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'SaaS';
    protected static ?string $navigationLabel = 'Users';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users';
    protected static bool $shouldRegisterNavigation = true;

    protected static function isSuperAdmin(): bool
    {
        return (bool) Filament::auth()->user()?->hasRole('super_admin');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Filament::auth()->user();
        return (bool) ($user?->hasAnyRole(['super_admin', 'admin', 'company_admin']));
    }

    protected static function isPortalPanel(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'portal';
    }

    public static function canCreate(): bool
    {
        $user = Filament::auth()->user();
        if (! $user) {
            return false;
        }

        // Explicitly allow creation for admins and company admins.
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

    public static function form(Forms\Form $form): Forms\Form
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

                    // Portal: يختبئ اختيار التينانت
                    // Admin Panel: إجباري تحديد التينانت
                    Select::make('tenant_id')
                        ->label('Company (Tenant)')
                        ->relationship('tenant', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->visible(fn() => ! static::isPortalPanel())
                        ->disabled(fn() => static::isPortalPanel()),

                    Select::make('roles')
                        ->label('Roles')
                        ->multiple()
                        ->relationship(
                            name: 'roles',
                            titleAttribute: 'name',
                            modifyQueryUsing: function ($query) {
                                $query->where('guard_name', 'web');

                                // Super admin يشاهد فقط الأدوار الإدارية
                                if (static::isSuperAdmin()) {
                                    $query->whereIn('name', ['super_admin', 'admin', 'company_admin']);
                                    return;
                                }

                                // باقي اللوحات: استبعاد super_admin
                                $query->where('name', '!=', 'super_admin');
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
                        ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn($state) => filled($state))
                        ->required(fn(string $operation) => $operation === 'create'),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
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

                // في Portal لا نعرض الشركة
                TextColumn::make('tenant.name')
                    ->label('Company')
                    ->sortable()
                    ->searchable()
                    ->visible(function (): bool {
                        $id = Auth::id();
                        if (! $id) {
                            return false;
                        }

                        return DB::table('model_has_roles')
                            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->where('model_has_roles.model_type', User::class)
                            ->where('model_has_roles.model_id', $id)
                            ->where('roles.name', 'super_admin')
                            ->exists();
                    }),


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
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (! static::isPortalPanel()) {
            return $data;
        }

        /** @var User|null $u */
        $u = Auth::user();

        $data['tenant_id'] = Filament::getTenant()?->id ?? $u?->tenant_id;

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (! static::isPortalPanel()) {
            return $data;
        }

        /** @var User|null $u */
        $u = Auth::user();

        $data['tenant_id'] = Filament::getTenant()?->id ?? $u?->tenant_id;

        return $data;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $authId = Auth::id();

        $isSuper = $authId
            ? DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_type', User::class)
                ->where('model_has_roles.model_id', $authId)
                ->where('roles.name', 'super_admin')
                ->exists()
            : false;

        // سوبر أدمن: يشاهد فقط الأدوار الإدارية
        if ($isSuper) {
            return $query->whereHas('roles', fn($q) => $q->whereIn('name', [
                'super_admin',
                'admin',
                'company_admin',
            ]));
        }

        // باقي اللوحات: حصر بيانات التينانت الحالي واستبعاد super_admin
        /** @var User|null $u */
        $u = Auth::user();

        $tenantId = Filament::getTenant()?->id ?? $u?->tenant_id;

        return $query
            ->where('tenant_id', $tenantId)
            ->whereDoesntHave('roles', fn($q) => $q->where('name', 'super_admin'));
    }
}
