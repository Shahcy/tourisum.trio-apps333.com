<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\Concerns\ScopesToTenant;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class BookingResource extends Resource
{
    use ScopesToTenant;

    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return __('bookings.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('bookings.booking.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('bookings.booking.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('bookings.booking.plural');
    }

    protected static function resolveTenantId(?Booking $record = null): ?int
    {
        return $record?->tenant_id
            ?? Filament::getTenant()?->id
            ?? Auth::user()?->tenant_id;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('BookingTabs')
                ->tabs([
                    Tabs\Tab::make(__('bookings.booking.tabs.overview'))
                        ->schema([
                            Section::make(__('bookings.booking.sections.booking_data'))
                                ->columns(3)
                                ->schema([
                                    TextInput::make('booking_number')
                                        ->label(__('bookings.booking.fields.booking_number'))
                                        ->disabled()
                                        ->dehydrated(),

                                    Select::make('booking_type')
                                        ->label(__('bookings.booking.fields.booking_type'))
                                        ->options([
                                            'domestic' => __('bookings.booking.types.domestic'),
                                            'international' => __('bookings.booking.types.international'),
                                            'umrah' => __('bookings.booking.types.umrah'),
                                            'corporate' => __('bookings.booking.types.corporate'),
                                        ]),

                                    Select::make('channel')
                                        ->label(__('bookings.booking.fields.channel'))
                                        ->options([
                                            'walk_in' => __('bookings.booking.channels.walk_in'),
                                            'whatsapp' => __('bookings.booking.channels.whatsapp'),
                                            'website' => __('bookings.booking.channels.website'),
                                            'partner' => __('bookings.booking.channels.partner'),
                                        ]),

                                    Select::make('agent_id')
                                        ->label(__('bookings.booking.fields.agent'))
                                        ->searchable()
                                        ->preload()
                                        ->options(function () {
                                            $tenantId = static::resolveTenantId();
                                            if (! $tenantId) {
                                                return [];
                                            }

                                            return User::query()
                                                ->where('tenant_id', $tenantId)
                                                ->orderBy('name')
                                                ->pluck('name', 'id')
                                                ->toArray();
                                        }),

                                    TextInput::make('branch')
                                        ->label(__('bookings.booking.fields.branch'))
                                        ->maxLength(120),

                                    Select::make('priority')
                                        ->label(__('bookings.booking.fields.priority'))
                                        ->options([
                                            'normal' => __('bookings.booking.priorities.normal'),
                                            'high' => __('bookings.booking.priorities.high'),
                                        ])
                                        ->default('normal'),

                                    TagsInput::make('tags')
                                        ->label(__('bookings.booking.fields.tags')),

                                    TextInput::make('source_campaign')
                                        ->label(__('bookings.booking.fields.source_campaign'))
                                        ->maxLength(255),

                                    Select::make('status')
                                        ->label(__('bookings.booking.fields.status'))
                                        ->required()
                                        ->default('draft')
                                        ->options([
                                            'draft' => __('bookings.booking.statuses.draft'),
                                            'confirmed' => __('bookings.booking.statuses.confirmed'),
                                            'cancelled' => __('bookings.booking.statuses.cancelled'),
                                            'completed' => __('bookings.booking.statuses.completed'),
                                        ]),

                                    TextInput::make('reference')
                                        ->label(__('bookings.booking.fields.reference'))
                                        ->maxLength(255),

                                    TextInput::make('destination')
                                        ->label(__('bookings.booking.fields.destination'))
                                        ->maxLength(255),

                                    DatePicker::make('start_date')
                                        ->label(__('bookings.booking.fields.start_date')),

                                    DatePicker::make('end_date')
                                        ->label(__('bookings.booking.fields.end_date')),

                                    TextInput::make('adults')
                                        ->label(__('bookings.booking.fields.adults'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1),

                                    TextInput::make('children')
                                        ->label(__('bookings.booking.fields.children'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->default(0),
                                ]),
                        ]),
                    Tabs\Tab::make(__('bookings.booking.tabs.customer_contacts'))
                        ->schema([
                            Section::make(__('bookings.booking.sections.customer'))
                                ->columns(2)
                                ->schema([
                                    Select::make('customer_id')
                                        ->label(__('bookings.booking.fields.customer'))
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            if (! $state) {
                                                return;
                                            }

                                            $customer = Customer::query()->find($state);
                                            if (! $customer) {
                                                return;
                                            }

                                            $primaryPhone = $customer->phone ?: $customer->alt_phone;
                                            $primaryEmail = $customer->email ?: $customer->alt_email;

                                            $currentContacts = $get('contacts') ?? [];
                                            if (empty($currentContacts)) {
                                                $preferred = $primaryPhone ? 'whatsapp' : ($primaryEmail ? 'email' : null);
                                                $set('contacts', [[
                                                    'name' => $customer->full_name,
                                                    'phone' => $primaryPhone,
                                                    'email' => $primaryEmail,
                                                    'relation_type' => 'customer',
                                                    'preferred_contact' => $preferred,
                                                    'billing_address' => null,
                                                    'company_name' => null,
                                                ]]);
                                            }

                                            $currentTravelers = $get('travelers') ?? [];
                                            if (empty($currentTravelers)) {
                                                $docType = $customer->document_type;
                                                $docNumber = $customer->document_number ?: $customer->passport_number;
                                                $docExpiry = $customer->document_expiry ?: $customer->passport_expiry;
                                                $passportNumber = $docType === 'passport' ? $docNumber : ($customer->passport_number ?? null);
                                                $passportExpiry = $docType === 'passport' ? $docExpiry : ($customer->passport_expiry ?? null);
                                                $nationalId = $docType === 'id' ? $docNumber : null;

                                                $set('travelers', [[
                                                    'full_name' => $customer->full_name,
                                                    'date_of_birth' => $customer->date_of_birth?->format('Y-m-d'),
                                                    'nationality' => $customer->nationality,
                                                    'gender' => $customer->gender,
                                                    'role' => 'adult',
                                                    'passport_number' => $passportNumber,
                                                    'passport_expiry' => $passportExpiry?->format('Y-m-d'),
                                                    'national_id' => $nationalId,
                                                    'special_requests' => null,
                                                ]]);
                                            }
                                        })
                                        ->options(function () {
                                            $tenantId = static::resolveTenantId();
                                            if (! $tenantId) {
                                                return [];
                                            }

                                            return Customer::query()
                                                ->where('tenant_id', $tenantId)
                                                ->orderBy('full_name')
                                                ->pluck('full_name', 'id')
                                                ->toArray();
                                        }),
                                ]),
                            Section::make(__('bookings.booking.sections.contacts'))
                                ->schema([
                                    Repeater::make('contacts')
                                        ->label(__('bookings.booking.sections.contacts'))
                                        ->relationship()
                                        ->schema([
                                            TextInput::make('name')
                                                ->label(__('bookings.booking.fields.contact_name'))
                                                ->required(),
                                            TextInput::make('phone')
                                                ->label(__('bookings.booking.fields.contact_phone')),
                                            TextInput::make('email')
                                                ->label(__('bookings.booking.fields.contact_email'))
                                                ->email(),
                                            Select::make('relation_type')
                                                ->label(__('bookings.booking.fields.contact_relation'))
                                                ->options([
                                                    'customer' => __('bookings.booking.contact_relations.customer'),
                                                    'emergency' => __('bookings.booking.contact_relations.emergency'),
                                                    'company_hr' => __('bookings.booking.contact_relations.company_hr'),
                                                ]),
                                            Select::make('preferred_contact')
                                                ->label(__('bookings.booking.fields.preferred_contact'))
                                                ->options([
                                                    'whatsapp' => __('bookings.booking.channels.whatsapp'),
                                                    'call' => __('bookings.booking.channels.call'),
                                                    'email' => __('bookings.booking.channels.email'),
                                                ]),
                                            Textarea::make('billing_address')
                                                ->label(__('bookings.booking.fields.billing_address'))
                                                ->rows(2),
                                            TextInput::make('company_name')
                                                ->label(__('bookings.booking.fields.company_name')),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(0)
                                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data, ?Booking $record): array {
                                            $data['tenant_id'] = static::resolveTenantId($record);
                                            return $data;
                                        }),
                                ]),
                        ]),

                    Tabs\Tab::make(__('bookings.booking.tabs.travelers'))
                        ->schema([
                            Section::make(__('bookings.booking.sections.travelers'))
                                ->schema([
                                    Repeater::make('travelers')
                                        ->label(__('bookings.booking.sections.travelers'))
                                        ->relationship()
                                        ->schema([
                                            TextInput::make('full_name')
                                                ->label(__('bookings.booking.fields.traveler_name'))
                                                ->required(),
                                            DatePicker::make('date_of_birth')
                                                ->label(__('bookings.booking.fields.traveler_dob')),
                                            TextInput::make('nationality')
                                                ->label(__('bookings.booking.fields.traveler_nationality')),
                                            Select::make('gender')
                                                ->label(__('bookings.booking.fields.traveler_gender'))
                                                ->options([
                                                    'male' => __('bookings.booking.genders.male'),
                                                    'female' => __('bookings.booking.genders.female'),
                                                ]),
                                            Select::make('role')
                                                ->label(__('bookings.booking.fields.traveler_role'))
                                                ->options([
                                                    'adult' => __('bookings.booking.traveler_roles.adult'),
                                                    'child' => __('bookings.booking.traveler_roles.child'),
                                                    'infant' => __('bookings.booking.traveler_roles.infant'),
                                                ]),
                                            TextInput::make('passport_number')
                                                ->label(__('bookings.booking.fields.passport_number')),
                                            DatePicker::make('passport_expiry')
                                                ->label(__('bookings.booking.fields.passport_expiry')),
                                            TextInput::make('national_id')
                                                ->label(__('bookings.booking.fields.national_id')),
                                            Textarea::make('special_requests')
                                                ->label(__('bookings.booking.fields.special_requests'))
                                                ->rows(2),
                                            Repeater::make('documents')
                                                ->label(__('bookings.booking.sections.traveler_documents'))
                                                ->relationship()
                                                ->schema([
                                                    Select::make('document_type')
                                                        ->label(__('bookings.booking.fields.document_type'))
                                                        ->options([
                                                            'passport' => __('bookings.booking.document_types.passport'),
                                                            'national_id' => __('bookings.booking.document_types.national_id'),
                                                            'visa' => __('bookings.booking.document_types.visa'),
                                                            'ticket' => __('bookings.booking.document_types.ticket'),
                                                            'voucher' => __('bookings.booking.document_types.voucher'),
                                                            'other' => __('bookings.booking.document_types.other'),
                                                        ])
                                                        ->required(),
                                                    FileUpload::make('file_path')
                                                        ->label(__('bookings.booking.fields.document_file'))
                                                        ->disk('public')
                                                        ->directory('booking-documents')
                                                        ->preserveFilenames()
                                                        ->downloadable()
                                                        ->required(),
                                                    Select::make('visibility')
                                                        ->label(__('bookings.booking.fields.visibility'))
                                                        ->options([
                                                            'internal' => __('bookings.booking.visibility.internal'),
                                                            'customer' => __('bookings.booking.visibility.customer'),
                                                        ])
                                                        ->default('internal'),
                                                    DatePicker::make('expires_at')
                                                        ->label(__('bookings.booking.fields.expires_at')),
                                                ])
                                                ->columns(2)
                                                ->defaultItems(0)
                                                ->mutateRelationshipDataBeforeCreateUsing(function (array $data, ?Booking $record): array {
                                                    $data['tenant_id'] = static::resolveTenantId($record);
                                                    return $data;
                                                }),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(0)
                                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data, ?Booking $record): array {
                                            $data['tenant_id'] = static::resolveTenantId($record);
                                            return $data;
                                        }),
                                ]),
                        ]),
                    Tabs\Tab::make(__('bookings.booking.tabs.services_items'))
                        ->schema([
                            Section::make(__('bookings.booking.sections.items'))
                                ->schema([
                                    Repeater::make('items')
                                        ->label(__('bookings.booking.sections.items'))
                                        ->relationship()
                                        ->schema([
                                            Select::make('item_type')
                                                ->label(__('bookings.booking.fields.item_type'))
                                                ->options([
                                                    'flight' => __('bookings.booking.items.flight'),
                                                    'hotel' => __('bookings.booking.items.hotel'),
                                                    'transfer' => __('bookings.booking.items.transfer'),
                                                    'tour' => __('bookings.booking.items.tour'),
                                                    'visa' => __('bookings.booking.items.visa'),
                                                    'insurance' => __('bookings.booking.items.insurance'),
                                                    'car_rental' => __('bookings.booking.items.car_rental'),
                                                    'addon' => __('bookings.booking.items.addon'),
                                                ])
                                                ->required(),
                                            TextInput::make('supplier_name')
                                                ->label(__('bookings.booking.fields.supplier_name')),
                                            TextInput::make('supplier_contact')
                                                ->label(__('bookings.booking.fields.supplier_contact')),
                                            DatePicker::make('start_date')
                                                ->label(__('bookings.booking.fields.start_date')),
                                            DatePicker::make('end_date')
                                                ->label(__('bookings.booking.fields.end_date')),
                                            TextInput::make('quantity')
                                                ->label(__('bookings.booking.fields.quantity'))
                                                ->numeric()
                                                ->minValue(1)
                                                ->default(1),
                                            TextInput::make('cost_amount')
                                                ->label(__('bookings.booking.fields.cost_amount'))
                                                ->numeric()
                                                ->default(0),
                                            TextInput::make('price_amount')
                                                ->label(__('bookings.booking.fields.price_amount'))
                                                ->numeric()
                                                ->default(0),
                                            TextInput::make('taxes')
                                                ->label(__('bookings.booking.fields.taxes'))
                                                ->numeric()
                                                ->default(0),
                                            TextInput::make('fees')
                                                ->label(__('bookings.booking.fields.fees'))
                                                ->numeric()
                                                ->default(0),
                                            Select::make('status')
                                                ->label(__('bookings.booking.fields.item_status'))
                                                ->options([
                                                    'reserved' => __('bookings.booking.item_statuses.reserved'),
                                                    'ticketed' => __('bookings.booking.item_statuses.ticketed'),
                                                    'cancelled' => __('bookings.booking.item_statuses.cancelled'),
                                                ])
                                                ->default('reserved'),
                                            TextInput::make('reference')
                                                ->label(__('bookings.booking.fields.item_reference')),
                                            Textarea::make('notes')
                                                ->label(__('bookings.booking.fields.notes'))
                                                ->rows(2),
                                        ])
                                        ->columns(3)
                                        ->defaultItems(0)
                                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data, ?Booking $record): array {
                                            $data['tenant_id'] = static::resolveTenantId($record);
                                            return $data;
                                        }),
                                ]),
                        ]),

                    Tabs\Tab::make(__('bookings.booking.tabs.pricing_totals'))
                        ->schema([
                            Section::make(__('bookings.booking.sections.pricing'))
                                ->columns(3)
                                ->schema([
                                    TextInput::make('subtotal')
                                        ->label(__('bookings.booking.fields.subtotal'))
                                        ->numeric()
                                        ->default(0),
                                    TextInput::make('discount_amount')
                                        ->label(__('bookings.booking.fields.discount_amount'))
                                        ->numeric()
                                        ->default(0),
                                    TextInput::make('discount_percent')
                                        ->label(__('bookings.booking.fields.discount_percent'))
                                        ->numeric()
                                        ->default(0),
                                    TextInput::make('taxes')
                                        ->label(__('bookings.booking.fields.taxes'))
                                        ->numeric()
                                        ->default(0),
                                    TextInput::make('service_fees')
                                        ->label(__('bookings.booking.fields.service_fees'))
                                        ->numeric()
                                        ->default(0),
                                    TextInput::make('grand_total')
                                        ->label(__('bookings.booking.fields.grand_total'))
                                        ->numeric()
                                        ->default(0),
                                    TextInput::make('cost_total')
                                        ->label(__('bookings.booking.fields.cost_total'))
                                        ->numeric()
                                        ->default(0),
                                    Placeholder::make('profit_calc')
                                        ->label(__('bookings.booking.fields.profit'))
                                        ->content(function (?Booking $record, callable $get) {
                                            $grand = (float) ($record?->grand_total ?? $get('grand_total') ?? 0);
                                            $cost = (float) ($record?->cost_total ?? $get('cost_total') ?? 0);
                                            return number_format($grand - $cost, 2);
                                        }),
                                    Placeholder::make('profit_margin_calc')
                                        ->label(__('bookings.booking.fields.profit_margin'))
                                        ->content(function (?Booking $record, callable $get) {
                                            $grand = (float) ($record?->grand_total ?? $get('grand_total') ?? 0);
                                            $cost = (float) ($record?->cost_total ?? $get('cost_total') ?? 0);
                                            if ($grand <= 0) {
                                                return '0%';
                                            }
                                            $margin = (($grand - $cost) / $grand) * 100;
                                            return number_format($margin, 2) . '%';
                                        }),
                                    TextInput::make('currency')
                                        ->label(__('bookings.booking.fields.currency'))
                                        ->maxLength(10),
                                    TextInput::make('exchange_rate')
                                        ->label(__('bookings.booking.fields.exchange_rate'))
                                        ->numeric()
                                        ->default(1),
                                    Select::make('payment_status')
                                        ->label(__('bookings.booking.fields.payment_status'))
                                        ->options([
                                            'unpaid' => __('bookings.booking.payment_statuses.unpaid'),
                                            'partial' => __('bookings.booking.payment_statuses.partial'),
                                            'paid' => __('bookings.booking.payment_statuses.paid'),
                                            'overpaid' => __('bookings.booking.payment_statuses.overpaid'),
                                        ])
                                        ->default('unpaid'),
                                    Select::make('price_locked')
                                        ->label(__('bookings.booking.fields.price_locked'))
                                        ->options([
                                            0 => __('common.no'),
                                            1 => __('common.yes'),
                                        ])
                                        ->default(0),
                                ]),
                        ]),

                    Tabs\Tab::make(__('bookings.booking.tabs.payments'))
                        ->schema([
                            Section::make(__('bookings.booking.sections.payments'))
                                ->schema([
                                    Repeater::make('payments')
                                        ->label(__('bookings.booking.sections.payments'))
                                        ->relationship()
                                        ->schema([
                                            TextInput::make('amount')
                                                ->label(__('bookings.booking.fields.payment_amount'))
                                                ->numeric()
                                                ->required(),
                                            Select::make('method')
                                                ->label(__('bookings.booking.fields.payment_method'))
                                                ->options([
                                                    'cash' => __('bookings.booking.payment_methods.cash'),
                                                    'bank' => __('bookings.booking.payment_methods.bank'),
                                                    'online' => __('bookings.booking.payment_methods.online'),
                                                    'wallet' => __('bookings.booking.payment_methods.wallet'),
                                                ])
                                                ->required(),
                                            TextInput::make('reference')
                                                ->label(__('bookings.booking.fields.payment_reference')),
                                            Select::make('received_by')
                                                ->label(__('bookings.booking.fields.received_by'))
                                                ->options(function () {
                                                    $tenantId = static::resolveTenantId();
                                                    if (! $tenantId) {
                                                        return [];
                                                    }

                                                    return User::query()
                                                        ->where('tenant_id', $tenantId)
                                                        ->orderBy('name')
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                }),
                                            Forms\Components\DateTimePicker::make('received_at')
                                                ->label(__('bookings.booking.fields.received_at')),
                                            Select::make('status')
                                                ->label(__('bookings.booking.fields.payment_status'))
                                                ->options([
                                                    'received' => __('bookings.booking.payment_statuses.received'),
                                                    'pending' => __('bookings.booking.payment_statuses.pending'),
                                                    'failed' => __('bookings.booking.payment_statuses.failed'),
                                                ])
                                                ->default('received'),
                                            Select::make('is_refund')
                                                ->label(__('bookings.booking.fields.is_refund'))
                                                ->options([
                                                    0 => __('common.no'),
                                                    1 => __('common.yes'),
                                                ])
                                                ->default(0),
                                        ])
                                        ->columns(3)
                                        ->defaultItems(0)
                                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data, ?Booking $record): array {
                                            $data['tenant_id'] = static::resolveTenantId($record);
                                            return $data;
                                        }),
                                ]),
                        ]),
                    Tabs\Tab::make(__('bookings.booking.tabs.documents'))
                        ->schema([
                            Section::make(__('bookings.booking.sections.documents'))
                                ->schema([
                                    FileUpload::make('voucher_path')
                                        ->label(__('bookings.booking.fields.voucher'))
                                        ->disk('public')
                                        ->directory('vouchers')
                                        ->preserveFilenames()
                                        ->downloadable(),

                                    Repeater::make('documents')
                                        ->label(__('bookings.booking.sections.documents'))
                                        ->relationship()
                                        ->schema([
                                            Select::make('document_type')
                                                ->label(__('bookings.booking.fields.document_type'))
                                                ->options([
                                                    'voucher' => __('bookings.booking.document_types.voucher'),
                                                    'ticket' => __('bookings.booking.document_types.ticket'),
                                                    'confirmation' => __('bookings.booking.document_types.confirmation'),
                                                    'visa' => __('bookings.booking.document_types.visa'),
                                                    'other' => __('bookings.booking.document_types.other'),
                                                ])
                                                ->required(),
                                            FileUpload::make('file_path')
                                                ->label(__('bookings.booking.fields.document_file'))
                                                ->disk('public')
                                                ->directory('booking-documents')
                                                ->preserveFilenames()
                                                ->downloadable()
                                                ->required(),
                                            Select::make('visibility')
                                                ->label(__('bookings.booking.fields.visibility'))
                                                ->options([
                                                    'internal' => __('bookings.booking.visibility.internal'),
                                                    'customer' => __('bookings.booking.visibility.customer'),
                                                ])
                                                ->default('internal'),
                                            DatePicker::make('expires_at')
                                                ->label(__('bookings.booking.fields.expires_at')),
                                            Textarea::make('notes')
                                                ->label(__('bookings.booking.fields.notes'))
                                                ->rows(2),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(0)
                                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data, ?Booking $record): array {
                                            $data['tenant_id'] = static::resolveTenantId($record);
                                            return $data;
                                        }),
                                ]),
                        ]),

                    Tabs\Tab::make(__('bookings.booking.tabs.timeline_notes'))
                        ->schema([
                            Section::make(__('bookings.booking.sections.notes'))
                                ->schema([
                                    Repeater::make('notes')
                                        ->label(__('bookings.booking.sections.notes'))
                                        ->relationship()
                                        ->schema([
                                            Select::make('note_type')
                                                ->label(__('bookings.booking.fields.note_type'))
                                                ->options([
                                                    'internal' => __('bookings.booking.note_types.internal'),
                                                    'customer' => __('bookings.booking.note_types.customer'),
                                                ])
                                                ->default('internal'),
                                            Select::make('channel')
                                                ->label(__('bookings.booking.fields.channel'))
                                                ->options([
                                                    'whatsapp' => __('bookings.booking.channels.whatsapp'),
                                                    'call' => __('bookings.booking.channels.call'),
                                                    'email' => __('bookings.booking.channels.email'),
                                                ]),
                                            Textarea::make('content')
                                                ->label(__('bookings.booking.fields.note_content'))
                                                ->required(),
                                            Textarea::make('next_step')
                                                ->label(__('bookings.booking.fields.next_step')),
                                            Forms\Components\DateTimePicker::make('follow_up_at')
                                                ->label(__('bookings.booking.fields.follow_up_at')),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(0)
                                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data, ?Booking $record): array {
                                            $data['tenant_id'] = static::resolveTenantId($record);
                                            return $data;
                                        }),
                                ]),
                            Section::make(__('bookings.booking.sections.timeline'))
                                ->schema([
                                    Placeholder::make('timeline')
                                        ->label('')
                                        ->content(function (?Booking $record) {
                                            if (! $record) {
                                                return new HtmlString(__('bookings.booking.timeline.empty'));
                                            }

                                            $items = $record->activities()
                                                ->latest()
                                                ->take(10)
                                                ->get()
                                                ->map(function ($activity) {
                                                    $time = $activity->created_at?->format('Y-m-d H:i');
                                                    return $time . ' - ' . $activity->description;
                                                })
                                                ->implode("\n");

                                            if ($items === '') {
                                                return new HtmlString(__('bookings.booking.timeline.empty'));
                                            }

                                            return new HtmlString(nl2br(e($items)));
                                        }),
                                ]),
                        ]),

                    Tabs\Tab::make(__('bookings.booking.tabs.tasks'))
                        ->schema([
                            Section::make(__('bookings.booking.sections.tasks'))
                                ->schema([
                                    Repeater::make('tasks')
                                        ->label(__('bookings.booking.sections.tasks'))
                                        ->relationship()
                                        ->schema([
                                            TextInput::make('title')
                                                ->label(__('bookings.booking.fields.task_title'))
                                                ->required(),
                                            Textarea::make('description')
                                                ->label(__('bookings.booking.fields.task_description')),
                                            DatePicker::make('due_date')
                                                ->label(__('bookings.booking.fields.task_due_date')),
                                            Select::make('assigned_to')
                                                ->label(__('bookings.booking.fields.task_assignee'))
                                                ->options(function () {
                                                    $tenantId = static::resolveTenantId();
                                                    if (! $tenantId) {
                                                        return [];
                                                    }

                                                    return User::query()
                                                        ->where('tenant_id', $tenantId)
                                                        ->orderBy('name')
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                }),
                                            Select::make('status')
                                                ->label(__('bookings.booking.fields.task_status'))
                                                ->options([
                                                    'open' => __('bookings.booking.task_statuses.open'),
                                                    'done' => __('bookings.booking.task_statuses.done'),
                                                ])
                                                ->default('open'),
                                            Forms\Components\DateTimePicker::make('completed_at')
                                                ->label(__('bookings.booking.fields.task_completed_at')),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(0)
                                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data, ?Booking $record): array {
                                            $data['tenant_id'] = static::resolveTenantId($record);
                                            return $data;
                                        }),
                                ]),
                        ]),

                    Tabs\Tab::make(__('bookings.booking.tabs.policies'))
                        ->schema([
                            Section::make(__('bookings.booking.sections.policies'))
                                ->columns(2)
                                ->schema([
                                    Textarea::make('cancellation_policy')
                                        ->label(__('bookings.booking.fields.cancellation_policy'))
                                        ->rows(3),
                                    Textarea::make('refund_rules')
                                        ->label(__('bookings.booking.fields.refund_rules'))
                                        ->rows(3),
                                    TextInput::make('cancellation_fee')
                                        ->label(__('bookings.booking.fields.cancellation_fee'))
                                        ->numeric()
                                        ->default(0),
                                    TextInput::make('refund_amount')
                                        ->label(__('bookings.booking.fields.refund_amount'))
                                        ->numeric()
                                        ->default(0),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('booking_number')
                    ->label(__('bookings.booking.fields.booking_number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.full_name')
                    ->label(__('bookings.booking.fields.customer'))
                    ->searchable()
                    ->sortable()
                    ->limit(25),
                TextColumn::make('booking_type')
                    ->label(__('bookings.booking.fields.booking_type'))
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'domestic' => __('bookings.booking.types.domestic'),
                        'international' => __('bookings.booking.types.international'),
                        'umrah' => __('bookings.booking.types.umrah'),
                        'corporate' => __('bookings.booking.types.corporate'),
                        default => (string) $state,
                    })
                    ->sortable(),
                TextColumn::make('channel')
                    ->label(__('bookings.booking.fields.channel'))
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'walk_in' => __('bookings.booking.channels.walk_in'),
                        'whatsapp' => __('bookings.booking.channels.whatsapp'),
                        'website' => __('bookings.booking.channels.website'),
                        'partner' => __('bookings.booking.channels.partner'),
                        default => (string) $state,
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('bookings.booking.fields.status'))
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'confirmed',
                        'danger' => 'cancelled',
                        'info' => 'completed',
                    ])
                    ->sortable(),
                TextColumn::make('grand_total')
                    ->label(__('bookings.booking.fields.grand_total'))
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label(__('bookings.booking.fields.payment_status'))
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'unpaid' => __('bookings.booking.payment_statuses.unpaid'),
                        'partial' => __('bookings.booking.payment_statuses.partial'),
                        'paid' => __('bookings.booking.payment_statuses.paid'),
                        'overpaid' => __('bookings.booking.payment_statuses.overpaid'),
                        default => (string) $state,
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('booking_type')
                    ->label(__('bookings.booking.fields.booking_type'))
                    ->options([
                        'domestic' => __('bookings.booking.types.domestic'),
                        'international' => __('bookings.booking.types.international'),
                        'umrah' => __('bookings.booking.types.umrah'),
                        'corporate' => __('bookings.booking.types.corporate'),
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('bookings.booking.fields.status'))
                    ->options([
                        'draft' => __('bookings.booking.statuses.draft'),
                        'confirmed' => __('bookings.booking.statuses.confirmed'),
                        'cancelled' => __('bookings.booking.statuses.cancelled'),
                        'completed' => __('bookings.booking.statuses.completed'),
                    ]),
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['type'])) {
            $data['type'] = $data['booking_type'] ?? 'domestic';
        }

        if (! array_key_exists('total_amount', $data) || $data['total_amount'] === null) {
            $data['total_amount'] = $data['grand_total'] ?? $data['subtotal'] ?? 0;
        }

        if (! array_key_exists('paid_amount', $data) || $data['paid_amount'] === null) {
            $data['paid_amount'] = 0;
        }

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['type'])) {
            $data['type'] = $data['booking_type'] ?? 'domestic';
        }

        if (! array_key_exists('total_amount', $data) || $data['total_amount'] === null) {
            $data['total_amount'] = $data['grand_total'] ?? $data['subtotal'] ?? 0;
        }

        return $data;
    }
}
