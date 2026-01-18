<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Customer;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Concerns\ScopesToTenant;


class BookingResource extends Resource
{
    use ScopesToTenant;
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    /**
     * Avoid hardcoded strings so locale switching works properly.
     */
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('bookings.booking.sections.booking_data'))
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('customer_id')
                        ->label(__('bookings.booking.fields.customer'))
                        ->required()
                        ->searchable()
                        ->preload()
                        ->options(function () {
                            $user = Filament::auth()->user();

                            return Customer::query()
                                ->where('tenant_id', $user->tenant_id)
                                ->orderBy('full_name')
                                ->pluck('full_name', 'id')
                                ->toArray();
                        }),

                    Forms\Components\Select::make('type')
                        ->label(__('bookings.booking.fields.type'))
                        ->required()
                        ->options([
                            'flight'    => __('bookings.booking.types.flight'),
                            'hotel'     => __('bookings.booking.types.hotel'),
                            'tour'      => __('bookings.booking.types.tour'),
                            'transport' => __('bookings.booking.types.transport'),
                            'group'     => __('bookings.booking.types.group_travel'),
                        ]),

                    Forms\Components\TextInput::make('reference')
                        ->label(__('bookings.booking.fields.reference'))
                        ->maxLength(255),

                    Forms\Components\TextInput::make('destination')
                        ->label(__('bookings.booking.fields.destination'))
                        ->maxLength(255),

                    Forms\Components\DatePicker::make('start_date')
                        ->label(__('bookings.booking.fields.start_date')),

                    Forms\Components\DatePicker::make('end_date')
                        ->label(__('bookings.booking.fields.end_date')),

                    Forms\Components\TextInput::make('adults')
                        ->label(__('bookings.booking.fields.adults'))
                        ->numeric()
                        ->minValue(1)
                        ->default(1),

                    Forms\Components\TextInput::make('children')
                        ->label(__('bookings.booking.fields.children'))
                        ->numeric()
                        ->minValue(0)
                        ->default(0),

                    Forms\Components\Select::make('status')
                        ->label(__('bookings.booking.fields.status'))
                        ->required()
                        ->default('draft')
                        ->options([
                            'draft'     => __('bookings.booking.statuses.draft'),
                            'confirmed' => __('bookings.booking.statuses.confirmed'),
                            'cancelled' => __('bookings.booking.statuses.cancelled'),
                            'completed' => __('bookings.booking.statuses.completed'),
                        ]),
                ]),

            Forms\Components\Section::make(__('bookings.booking.sections.payments'))
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('total_amount')
                        ->label(__('bookings.booking.fields.total_amount'))
                        ->numeric()
                        ->default(0)
                        ->prefix('$'),

                    Forms\Components\TextInput::make('paid_amount')
                        ->label(__('bookings.booking.fields.paid_amount'))
                        ->numeric()
                        ->default(0)
                        ->prefix('$'),

                    Forms\Components\Placeholder::make('remaining_amount')
                        ->label(__('bookings.booking.fields.remaining_amount'))
                        ->content(function (?Booking $record, callable $get) {
                            $total = (float) ($record?->total_amount ?? $get('total_amount') ?? 0);
                            $paid  = (float) ($record?->paid_amount ?? $get('paid_amount') ?? 0);
                            $remaining = max($total - $paid, 0);

                            return number_format($remaining, 2);
                        }),
                ]),

            Forms\Components\Section::make(__('bookings.booking.sections.files_notes'))
                ->schema([
                    Forms\Components\FileUpload::make('voucher_path')
                        ->label(__('bookings.booking.fields.voucher'))
                        ->disk('public')
                        ->directory('vouchers')
                        ->preserveFilenames()
                        ->downloadable(),

                    Forms\Components\Textarea::make('notes')
                        ->label(__('bookings.booking.fields.notes'))
                        ->rows(4),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('customer.full_name')
                    ->label(__('bookings.booking.fields.customer'))
                    ->searchable()
                    ->sortable()
                    ->limit(25),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('bookings.booking.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'flight'    => __('bookings.booking.types.flight'),
                        'hotel'     => __('bookings.booking.types.hotel'),
                        'tour'      => __('bookings.booking.types.tour'),
                        'transport' => __('bookings.booking.types.transport'),
                        'group'     => __('bookings.booking.types.group'),
                        default     => (string) $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('bookings.booking.fields.status'))
                    ->badge()
                    ->colors([
                        'gray'    => 'draft',
                        'success' => 'confirmed',
                        'danger'  => 'cancelled',
                        'info'    => 'completed',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('bookings.booking.fields.start_date_short'))
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label(__('bookings.booking.fields.end_date_short'))
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label(__('bookings.booking.fields.total_amount'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label(__('bookings.booking.fields.paid_amount'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_calc')
                    ->label(__('bookings.booking.fields.remaining_amount'))
                    ->state(fn(Booking $record) => max(((float) $record->total_amount) - ((float) $record->paid_amount), 0))
                    ->money('USD'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('bookings.booking.fields.type'))
                    ->options([
                        'flight'    => __('bookings.booking.types.flight'),
                        'hotel'     => __('bookings.booking.types.hotel'),
                        'tour'      => __('bookings.booking.types.tour'),
                        'transport' => __('bookings.booking.types.transport'),
                        'group'     => __('bookings.booking.types.group_travel'),
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label(__('bookings.booking.fields.status'))
                    ->options([
                        'draft'     => __('bookings.booking.statuses.draft'),
                        'confirmed' => __('bookings.booking.statuses.confirmed'),
                        'cancelled' => __('bookings.booking.statuses.cancelled'),
                        'completed' => __('bookings.booking.statuses.completed'),
                    ]),

                Tables\Filters\Filter::make('has_remaining')
                    ->label(__('bookings.booking.filters.has_remaining'))
                    ->query(fn(Builder $query) => $query->whereColumn('paid_amount', '<', 'total_amount')),
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
}
