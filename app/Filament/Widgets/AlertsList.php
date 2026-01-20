<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Support\TenantContext;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AlertsList extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?string $heading = 'Overdue Invoices (requires due date)';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $tenantId = TenantContext::id();

        // إذا ما في tenant context، لا تعرض شيء (بدون تسريب بيانات)
        if (! $tenantId) {
            return $table->query(Invoice::query()->whereRaw('1=0'));
        }

        return $table
            ->query(
                Invoice::query()
                    ->with(['customer:id,full_name'])
                    ->where('tenant_id', $tenantId)
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', today())
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->orderBy('due_date', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label(__('Invoice Number'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.full_name')
                    ->label(__('Customer'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label(__('Due Date'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label(__('Total'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label(__('Paid Amount'))
                    ->numeric(),

                Tables\Columns\TextColumn::make('remaining')
                    ->label(__('Remaining'))
                    ->numeric(),
            ])
            ->defaultPaginationPageOption(5)
            ->paginated([5]);
    }

    protected function getHeading(): string
    {
        return __('Overdue Invoices (requires due date)');
    }
}
