<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\ScopesToTenant;
use App\Filament\Resources\ProviderResource\Pages;
use App\Models\IntegrationLog;
use App\Models\Provider;
use App\Support\TenantContext;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProviderResource extends Resource
{
    use ScopesToTenant;

    protected static ?string $model = Provider::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';
    protected static ?int $navigationSort = 65;

    public static function getNavigationGroup(): ?string
    {
        return __('settings.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('providers.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('providers.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('providers.plural');
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return (bool) ($user?->hasAnyRole(['super_admin', 'admin', 'company_admin'])
            || $user?->can('providers.view'));
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        return (bool) ($user?->hasAnyRole(['super_admin', 'admin', 'company_admin'])
            || $user?->can('providers.manage'));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Filament::getCurrentPanel()?->getId() === 'portal') {
            return $query->where('tenant_id', TenantContext::requireId());
        }

        $user = Auth::user();
        if ($user?->hasRole('super_admin')) {
            return $query;
        }

        return $query->where('tenant_id', $user?->tenant_id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('providers.sections.general'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('providers.fields.name'))
                        ->disabled(),

                    Forms\Components\TextInput::make('code')
                        ->label(__('providers.fields.code'))
                        ->disabled(),

                    Forms\Components\Select::make('type')
                        ->label(__('providers.fields.type'))
                        ->options([
                            'gds' => __('providers.types.gds'),
                        ])
                        ->disabled(),

                    Forms\Components\Select::make('mode')
                        ->label(__('providers.fields.mode'))
                        ->options([
                            'manual' => __('providers.modes.manual'),
                            'api' => __('providers.modes.api'),
                        ])
                        ->live()
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->label(__('providers.fields.status'))
                        ->options([
                            'pending' => __('providers.statuses.pending'),
                            'connected' => __('providers.statuses.connected'),
                            'failed' => __('providers.statuses.failed'),
                            'disabled' => __('providers.statuses.disabled'),
                        ])
                        ->required(),
                ]),

            Forms\Components\Section::make(__('providers.sections.api'))
                ->columns(2)
                ->visible(fn(Get $get) => $get('mode') === 'api')
                ->schema([
                    Forms\Components\Select::make('config.environment')
                        ->label(__('providers.fields.environment'))
                        ->options([
                            'test' => __('providers.environments.test'),
                            'prod' => __('providers.environments.prod'),
                        ])
                        ->required(fn(Get $get) => $get('mode') === 'api'),

                    Forms\Components\TextInput::make('config.client_id')
                        ->label(__('providers.fields.client_id'))
                        ->password()
                        ->revealable()
                        ->required(function (Get $get, ?Provider $record) {
                            return $get('mode') === 'api' && ! $record?->getConfigValue('client_id');
                        })
                        ->afterStateHydrated(fn($state, callable $set) => $set('config.client_id', null))
                        ->dehydrateStateUsing(fn($state, ?Provider $record) => filled($state) ? $state : $record?->getConfigValue('client_id')),

                    Forms\Components\TextInput::make('config.client_secret')
                        ->label(__('providers.fields.client_secret'))
                        ->password()
                        ->revealable()
                        ->required(function (Get $get, ?Provider $record) {
                            return $get('mode') === 'api' && ! $record?->getConfigValue('client_secret');
                        })
                        ->afterStateHydrated(fn($state, callable $set) => $set('config.client_secret', null))
                        ->dehydrateStateUsing(fn($state, ?Provider $record) => filled($state) ? $state : $record?->getConfigValue('client_secret')),

                    Forms\Components\TextInput::make('config.office_id')
                        ->label(__('providers.fields.office_id'))
                        ->password()
                        ->revealable()
                        ->afterStateHydrated(fn($state, callable $set) => $set('config.office_id', null))
                        ->dehydrateStateUsing(fn($state, ?Provider $record) => filled($state) ? $state : $record?->getConfigValue('office_id')),

                    Forms\Components\Textarea::make('config.extra_json')
                        ->label(__('providers.fields.extra_json'))
                        ->rows(3),
                ]),

            Forms\Components\Section::make(__('providers.sections.status'))
                ->columns(2)
                ->schema([
                    Forms\Components\Placeholder::make('last_checked_at')
                        ->label(__('providers.fields.last_checked_at'))
                        ->content(fn(?Provider $record) => $record?->last_checked_at?->format('Y-m-d H:i') ?? '-'),

                    Forms\Components\Placeholder::make('last_error_message')
                        ->label(__('providers.fields.last_error_message'))
                        ->content(fn(?Provider $record) => $record?->last_error_message ?: '-'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('providers.fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label(__('providers.fields.code'))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('providers.fields.type'))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('mode')
                    ->label(__('providers.fields.mode'))
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'api' => __('providers.modes.api'),
                        default => __('providers.modes.manual'),
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('providers.fields.status'))
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'connected',
                        'danger' => 'failed',
                        'gray' => 'disabled',
                    ])
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'pending' => __('providers.statuses.pending'),
                        'connected' => __('providers.statuses.connected'),
                        'failed' => __('providers.statuses.failed'),
                        'disabled' => __('providers.statuses.disabled'),
                        default => (string) $state,
                    }),

                Tables\Columns\TextColumn::make('last_checked_at')
                    ->label(__('providers.fields.last_checked_at'))
                    ->dateTime()
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('test_connection')
                    ->label(__('providers.actions.test_connection'))
                    ->icon('heroicon-o-signal')
                    ->visible(fn() => Auth::user()?->can('providers.manage'))
                    ->requiresConfirmation()
                    ->action(function (Provider $record) {
                        $payload = [
                            'environment' => $record->getConfigValue('environment'),
                            'provider_code' => $record->code,
                        ];

                        if ($record->mode !== 'api') {
                            Notification::make()
                                ->title(__('providers.notifications.api_only'))
                                ->warning()
                                ->send();
                            return;
                        }

                        $clientId = $record->getConfigValue('client_id');
                        $clientSecret = $record->getConfigValue('client_secret');

                        if (! $clientId || ! $clientSecret) {
                            $record->forceFill([
                                'status' => 'failed',
                                'last_checked_at' => now(),
                                'last_error_message' => __('providers.errors.missing_credentials'),
                            ])->save();

                            IntegrationLog::create([
                                'tenant_id' => $record->tenant_id,
                                'entity_type' => 'provider',
                                'entity_id' => $record->id,
                                'action' => 'test_connection',
                                'status' => 'fail',
                                'request_payload' => $payload,
                                'response_payload' => ['stub' => true],
                                'error_message' => __('providers.errors.missing_credentials'),
                            ]);

                            Notification::make()
                                ->title(__('providers.notifications.test_failed'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->forceFill([
                            'status' => 'connected',
                            'last_checked_at' => now(),
                            'last_error_message' => null,
                        ])->save();

                        IntegrationLog::create([
                            'tenant_id' => $record->tenant_id,
                            'entity_type' => 'provider',
                            'entity_id' => $record->id,
                            'action' => 'test_connection',
                            'status' => 'success',
                            'request_payload' => $payload,
                            'response_payload' => ['stub' => true],
                            'error_message' => null,
                        ]);

                        Notification::make()
                            ->title(__('providers.notifications.test_success'))
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->visible(fn() => Auth::user()?->can('providers.manage')),
            ])
            ->defaultSort('id');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProviders::route('/'),
            'edit' => Pages\EditProvider::route('/{record}/edit'),
        ];
    }
}
