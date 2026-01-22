<?php

namespace App\Filament\Widgets\Reports;

use App\Models\Payment;
use App\Support\TenantContext;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentPaymentsTable extends TableWidget
{
    protected static ?string $heading = 'reports.widgets.recent_payments';
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $tenantId = TenantContext::id();
        if (! $tenantId) {
            return Payment::query()->whereRaw('1 = 0');
        }

        return Payment::query()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('date')
            ->limit(20);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('date')
                ->label(__('finance.payments.fields.date'))
                ->date()
                ->sortable(),

            Tables\Columns\TextColumn::make('direction')
                ->label(__('finance.payments.fields.direction'))
                ->badge()
                ->formatStateUsing(fn(?string $state) => match ($state) {
                    'out' => __('finance.payments.directions.out'),
                    default => __('finance.payments.directions.in'),
                }),

            Tables\Columns\TextColumn::make('amount')
                ->label(__('finance.payments.fields.amount'))
                ->formatStateUsing(fn($state) => number_format((float) $state, 2)),

            Tables\Columns\TextColumn::make('method')
                ->label(__('finance.payments.fields.method'))
                ->formatStateUsing(fn(?string $state) => match ($state) {
                    'cash' => __('finance.payments.methods.cash'),
                    'bank' => __('finance.payments.methods.bank'),
                    'card' => __('finance.payments.methods.card'),
                    'wallet' => __('finance.payments.methods.wallet'),
                    'other' => __('finance.payments.methods.other'),
                    default => (string) $state,
                }),

            Tables\Columns\TextColumn::make('account.name')
                ->label(__('finance.payments.fields.account'))
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected function getTableHeading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return static::$heading ? __(static::$heading) : null;
    }
}
