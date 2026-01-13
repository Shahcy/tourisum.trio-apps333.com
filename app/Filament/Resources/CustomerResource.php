<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'العملاء';
    protected static ?string $modelLabel = 'عميل';
    protected static ?string $pluralModelLabel = 'العملاء';

    /**
     * SaaS: فلترة كل الاستعلامات حسب tenant الحالي
     */
    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

        return parent::getEloquentQuery()
            ->where('tenant_id', $user->tenant_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات العميل')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->label('الاسم الكامل')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'new' => 'New',
                                'lead' => 'Lead',
                                'interested' => 'Interested',
                                'booked' => 'Booked',
                                'lost' => 'Lost',
                            ])
                            ->required()
                            ->default('new'),

                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('nationality')
                            ->label('الجنسية')
                            ->maxLength(100),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('الجواز والتأشيرة')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('passport_number')
                            ->label('رقم الجواز')
                            ->maxLength(100),

                        Forms\Components\DatePicker::make('passport_expiry')
                            ->label('تاريخ انتهاء الجواز'),

                        Forms\Components\TextInput::make('visa_type')
                            ->label('نوع التأشيرة')
                            ->maxLength(100),

                        Forms\Components\DatePicker::make('visa_expiry')
                            ->label('تاريخ انتهاء التأشيرة'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('الإيميل')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->colors([
                        'gray' => 'new',
                        'warning' => 'lead',
                        'info' => 'interested',
                        'success' => 'booked',
                        'danger' => 'lost',
                    ])
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'new' => 'New',
                        'lead' => 'Lead',
                        'interested' => 'Interested',
                        'booked' => 'Booked',
                        'lost' => 'Lost',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('passport_expiry')
                    ->label('انتهاء الجواز')
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->color(fn($record) => self::expiryColor($record->passport_expiry))
                    ->description(fn($record) => self::expiryText($record->passport_expiry)),

                Tables\Columns\TextColumn::make('visa_expiry')
                    ->label('انتهاء التأشيرة')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color(fn($record) => self::expiryColor($record->visa_expiry))
                    ->description(fn($record) => self::expiryText($record->visa_expiry)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'new' => 'New',
                        'lead' => 'Lead',
                        'interested' => 'Interested',
                        'booked' => 'Booked',
                        'lost' => 'Lost',
                    ]),

                Tables\Filters\Filter::make('passport_expired')
                    ->label('الجواز منتهي')
                    ->query(fn(Builder $query) => $query
                        ->whereNotNull('passport_expiry')
                        ->whereDate('passport_expiry', '<', now()->toDateString())),

                Tables\Filters\Filter::make('passport_expiring_30')
                    ->label('الجواز سينتهي خلال 30 يوم')
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
        if (!$date) return null;

        $d = \Illuminate\Support\Carbon::parse($date)->startOfDay();
        $today = now()->startOfDay();

        if ($d->lt($today)) return 'danger';
        if ($d->lte($today->copy()->addDays(30))) return 'warning';
        return 'success';
    }

    protected static function expiryText($date): ?string
    {
        if (!$date) return null;

        $d = \Illuminate\Support\Carbon::parse($date)->startOfDay();
        $today = now()->startOfDay();

        if ($d->lt($today)) return 'منتهي';

        $days = $today->diffInDays($d);
        if ($days <= 30) return "متبقي {$days} يوم";

        return null;
    }
}
