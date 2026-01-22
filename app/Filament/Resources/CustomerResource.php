<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Concerns\ScopesToTenant;

class CustomerResource extends Resource
{
    use ScopesToTenant;
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 20;

    /**
     * Avoid hardcoded strings so locale switching works properly.
     */
    public static function getNavigationGroup(): ?string
    {
        return __('crm.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('crm.customers.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('crm.customers.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('crm.customers.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('crm.customers.sections.customer_data'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('Company')
                            ->relationship('tenant', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn() => auth()->user()?->tenant_id)
                            ->required(fn() => auth()->user()?->tenant_id === null)
                            ->visible(fn() => ! static::isPortalPanel() && auth()->user()?->tenant_id === null),

                        Forms\Components\TextInput::make('full_name')
                            ->label(__('crm.customers.fields.full_name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('status')
                            ->label(__('crm.customers.fields.status'))
                            ->options([
                                'new'        => __('crm.customers.statuses.new'),
                                'lead'       => __('crm.customers.statuses.lead'),
                                'interested' => __('crm.customers.statuses.interested'),
                                'booked'     => __('crm.customers.statuses.booked'),
                                'lost'       => __('crm.customers.statuses.lost'),
                            ])
                            ->required()
                            ->default('new'),

                        Forms\Components\TextInput::make('phone')
                            ->label(__('crm.customers.fields.phone'))
                            ->tel()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('email')
                            ->label(__('crm.customers.fields.email'))
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('nationality')
                            ->label(__('crm.customers.fields.nationality'))
                            ->maxLength(100),

                        Forms\Components\TextInput::make('address')
                            ->label(__('crm.customers.fields.address'))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('company_name')
                            ->label(__('crm.customers.fields.company_name'))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('alt_phone')
                            ->label(__('crm.customers.fields.alt_phone'))
                            ->tel()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('alt_email')
                            ->label(__('crm.customers.fields.alt_email'))
                            ->email()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('notes')
                            ->label(__('crm.customers.fields.notes'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make(__('crm.customers.sections.identity'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('document_type')
                            ->label(__('crm.customers.fields.document_type'))
                            ->options([
                                'id' => __('crm.customers.document_types.id'),
                                'passport' => __('crm.customers.document_types.passport'),
                            ]),

                        Forms\Components\TextInput::make('document_number')
                            ->label(__('crm.customers.fields.document_number'))
                            ->maxLength(100),

                        Forms\Components\DatePicker::make('document_expiry')
                            ->label(__('crm.customers.fields.document_expiry')),

                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label(__('crm.customers.fields.date_of_birth')),

                        Forms\Components\Select::make('gender')
                            ->label(__('crm.customers.fields.gender'))
                            ->options([
                                'male' => __('crm.customers.genders.male'),
                                'female' => __('crm.customers.genders.female'),
                            ]),
                    ]),

                Forms\Components\Section::make(__('crm.customers.sections.passport_visa'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('passport_number')
                            ->label(__('crm.customers.fields.passport_number'))
                            ->visible(fn(Get $get) => $get('document_type') !== 'passport')
                            ->maxLength(100),

                        Forms\Components\DatePicker::make('passport_expiry')
                            ->label(__('crm.customers.fields.passport_expiry'))
                            ->visible(fn(Get $get) => $get('document_type') !== 'passport'),

                        Forms\Components\TextInput::make('visa_type')
                            ->label(__('crm.customers.fields.visa_type'))
                            ->maxLength(100),

                        Forms\Components\DatePicker::make('visa_expiry')
                            ->label(__('crm.customers.fields.visa_expiry')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('crm.customers.fields.name_short'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('phone')
                    ->label(__('crm.customers.fields.phone'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('crm.customers.fields.email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('crm.customers.fields.status'))
                    ->badge()
                    ->colors([
                        'gray'    => 'new',
                        'warning' => 'lead',
                        'info'    => 'interested',
                        'success' => 'booked',
                        'danger'  => 'lost',
                    ])
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'new'        => __('crm.customers.statuses.new'),
                        'lead'       => __('crm.customers.statuses.lead'),
                        'interested' => __('crm.customers.statuses.interested'),
                        'booked'     => __('crm.customers.statuses.booked'),
                        'lost'       => __('crm.customers.statuses.lost'),
                        default      => (string) $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('passport_expiry')
                    ->label(__('crm.customers.fields.passport_expiry_short'))
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->color(fn($record) => self::expiryColor($record->passport_expiry))
                    ->description(fn($record) => self::expiryText($record->passport_expiry)),

                Tables\Columns\TextColumn::make('visa_expiry')
                    ->label(__('crm.customers.fields.visa_expiry_short'))
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color(fn($record) => self::expiryColor($record->visa_expiry))
                    ->description(fn($record) => self::expiryText($record->visa_expiry)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('crm.customers.fields.status'))
                    ->options([
                        'new'        => __('crm.customers.statuses.new'),
                        'lead'       => __('crm.customers.statuses.lead'),
                        'interested' => __('crm.customers.statuses.interested'),
                        'booked'     => __('crm.customers.statuses.booked'),
                        'lost'       => __('crm.customers.statuses.lost'),
                    ]),

                Tables\Filters\Filter::make('passport_expired')
                    ->label(__('crm.customers.filters.passport_expired'))
                    ->query(fn(Builder $query) => $query
                        ->whereNotNull('passport_expiry')
                        ->whereDate('passport_expiry', '<', now()->toDateString())),

                Tables\Filters\Filter::make('passport_expiring_30')
                    ->label(__('crm.customers.filters.passport_expiring_30'))
                    ->query(fn(Builder $query) => $query
                        ->whereNotNull('passport_expiry')
                        ->whereBetween('passport_expiry', [
                            now()->toDateString(),
                            now()->addDays(30)->toDateString(),
                        ])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    protected static function expiryColor($date): ?string
    {
        if (!$date) {
            return null;
        }

        $d = \Illuminate\Support\Carbon::parse($date)->startOfDay();
        $today = now()->startOfDay();

        if ($d->lt($today)) {
            return 'danger';
        }

        if ($d->lte($today->copy()->addDays(30))) {
            return 'warning';
        }

        return 'success';
    }

    protected static function expiryText($date): ?string
    {
        if (!$date) {
            return null;
        }

        $d = \Illuminate\Support\Carbon::parse($date)->startOfDay();
        $today = now()->startOfDay();

        if ($d->lt($today)) {
            return __('crm.customers.expiry.expired');
        }

        $days = $today->diffInDays($d);

        if ($days <= 30) {
            return __('crm.customers.expiry.remaining_days', ['days' => $days]);
        }

        return null;
    }
}
