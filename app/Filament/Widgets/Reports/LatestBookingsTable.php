<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Booking;
use App\Support\TenantContext;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestBookingsTable extends TableWidget
{
    protected static ?string $heading = 'reports.widgets.latest_bookings';
    protected static bool $isLazy = true;

    protected function getTableQuery(): Builder
    {
        $tenantId = TenantContext::id();
        if (! $tenantId) {
            return Booking::query()->whereRaw('1 = 0');
        }

        return Booking::query()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->limit(20);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('booking_number')
                ->label(__('bookings.booking.fields.booking_number'))
                ->searchable(),

            Tables\Columns\TextColumn::make('customer.full_name')
                ->label(__('bookings.booking.fields.customer'))
                ->searchable(),

            Tables\Columns\TextColumn::make('status')
                ->label(__('bookings.booking.fields.status'))
                ->badge()
                ->formatStateUsing(fn(?string $state) => match ($state) {
                    'draft' => __('bookings.booking.statuses.draft'),
                    'confirmed' => __('bookings.booking.statuses.confirmed'),
                    'completed' => __('bookings.booking.statuses.completed'),
                    'cancelled' => __('bookings.booking.statuses.cancelled'),
                    default => (string) $state,
                }),

            Tables\Columns\TextColumn::make('grand_total')
                ->label(__('bookings.booking.fields.grand_total'))
                ->formatStateUsing(fn($state) => number_format((float) $state, 2)),

            Tables\Columns\TextColumn::make('created_at')
                ->label(__('common.created_at'))
                ->dateTime(),
        ];
    }

    protected function getTableHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return static::$heading ? __(static::$heading) : null;
    }
}
