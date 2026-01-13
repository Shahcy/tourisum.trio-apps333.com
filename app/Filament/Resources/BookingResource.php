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

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Bookings';
    protected static ?string $navigationLabel = 'الحجوزات';
    protected static ?string $modelLabel = 'حجز';
    protected static ?string $pluralModelLabel = 'الحجوزات';

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

        return parent::getEloquentQuery()
            ->where('tenant_id', $user->tenant_id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات الحجز')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('customer_id')
                        ->label('العميل')
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
                        ->label('نوع الحجز')
                        ->required()
                        ->options([
                            'flight' => 'Flight',
                            'hotel' => 'Hotel',
                            'tour' => 'Tour',
                            'transport' => 'Transport',
                            'group' => 'Group Travel',
                        ]),

                    Forms\Components\TextInput::make('reference')
                        ->label('Reference')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('destination')
                        ->label('الوجهة')
                        ->maxLength(255),

                    Forms\Components\DatePicker::make('start_date')
                        ->label('تاريخ البداية'),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('تاريخ النهاية'),

                    Forms\Components\TextInput::make('adults')
                        ->label('Adults')
                        ->numeric()
                        ->minValue(1)
                        ->default(1),

                    Forms\Components\TextInput::make('children')
                        ->label('Children')
                        ->numeric()
                        ->minValue(0)
                        ->default(0),

                    Forms\Components\Select::make('status')
                        ->label('حالة الحجز')
                        ->required()
                        ->default('draft')
                        ->options([
                            'draft' => 'Draft',
                            'confirmed' => 'Confirmed',
                            'cancelled' => 'Cancelled',
                            'completed' => 'Completed',
                        ]),
                ]),

            Forms\Components\Section::make('المدفوعات')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('total_amount')
                        ->label('الإجمالي')
                        ->numeric()
                        ->default(0)
                        ->prefix('$'),

                    Forms\Components\TextInput::make('paid_amount')
                        ->label('المدفوع')
                        ->numeric()
                        ->default(0)
                        ->prefix('$'),

                    Forms\Components\Placeholder::make('remaining_amount')
                        ->label('المتبقي')
                        ->content(function (?Booking $record, callable $get) {
                            $total = (float) ($record?->total_amount ?? $get('total_amount') ?? 0);
                            $paid  = (float) ($record?->paid_amount ?? $get('paid_amount') ?? 0);
                            $remaining = max($total - $paid, 0);

                            return number_format($remaining, 2);
                        }),
                ]),

            Forms\Components\Section::make('ملفات وملاحظات')
                ->schema([
                    Forms\Components\FileUpload::make('voucher_path')
                        ->label('Voucher / PDF')
                        ->disk('public')
                        ->directory('vouchers')
                        ->preserveFilenames()
                        ->downloadable(),

                    Forms\Components\Textarea::make('notes')
                        ->label('ملاحظات')
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
                    ->label('العميل')
                    ->searchable()
                    ->sortable()
                    ->limit(25),

                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'flight' => 'Flight',
                        'hotel' => 'Hotel',
                        'tour' => 'Tour',
                        'transport' => 'Transport',
                        'group' => 'Group',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'confirmed',
                        'danger' => 'cancelled',
                        'info' => 'completed',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('البداية')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('النهاية')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('الإجمالي')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('المدفوع')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_calc')
                    ->label('المتبقي')
                    ->state(fn(Booking $record) => max(((float) $record->total_amount) - ((float) $record->paid_amount), 0))
                    ->money('USD'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'flight' => 'Flight',
                        'hotel' => 'Hotel',
                        'tour' => 'Tour',
                        'transport' => 'Transport',
                        'group' => 'Group Travel',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'Draft',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                    ]),

                Tables\Filters\Filter::make('has_remaining')
                    ->label('عليه متبقي')
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
