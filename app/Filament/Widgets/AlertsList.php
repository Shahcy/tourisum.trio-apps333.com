<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AlertsList extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?string $heading = 'تنبيهات التحصيل (فواتير متأخرة)';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $tenantId = Filament::getTenant()?->id ?? Filament::auth()->user()->tenant_id;

        return $table
            ->query(
                Invoice::query()
                    ->with(['customer:id,full_name'])   // مهم جداً لتسريع
                    ->select([
                        'id',
                        'tenant_id',
                        'customer_id',
                        'number',
                        'due_date',
                        'total',
                        'paid_amount',
                        'remaining',
                        'status',
                    ])
                    ->where('tenant_id', $tenantId)
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', today())
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->orderBy('due_date', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('رقم الفاتورة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.full_name')
                    ->label('العميل')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('الإجمالي')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('المدفوع')
                    ->numeric(),

                Tables\Columns\TextColumn::make('remaining')
                    ->label('المتبقي')
                    ->numeric(),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('فتح')
                    ->url(fn(Invoice $record) => route('filament.admin.resources.invoices.edit', [
                        'tenant' => (Filament::getTenant()?->getKey() ?? Filament::auth()->user()->tenant_id),
                        'record' => $record,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->defaultPaginationPageOption(5)
            ->paginated([5]); // خفف الخيارات لتقليل الحمل
    }
}
