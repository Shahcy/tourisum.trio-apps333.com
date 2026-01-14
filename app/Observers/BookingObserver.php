<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use Filament\Facades\Filament;

class BookingObserver
{
    public function created(Booking $booking): void
    {
        // إنشاء فاتورة تلقائياً عند إنشاء الحجز
        $invoice = Invoice::create([
            'tenant_id' => $booking->tenant_id,
            'customer_id' => $booking->customer_id,
            'booking_id' => $booking->id,
            'number' => $this->nextInvoiceNumber($booking->tenant_id),
            'issue_date' => now()->toDateString(),
            'status' => 'unpaid',
            'notes' => 'Auto-generated from booking #' . $booking->id,
        ]);

        $invoice->items()->create([
            'title' => $booking->type,
            'qty' => 1,
            'unit_price' => (float) $booking->total_amount,
        ]);

        $invoice->recalculateTotals();

        // إذا تم إدخال مدفوع عند الحجز، نسجل Payment قبض مربوط على الفاتورة
        $paid = (float) $booking->paid_amount;
        if ($paid > 0) {
            $accountId = $this->resolveDefaultCashAccountId($booking->tenant_id);

            Payment::create([
                'tenant_id' => $booking->tenant_id,
                'direction' => 'in',
                'date' => now()->toDateString(),
                'amount' => $paid,
                'method' => 'cash',
                'account_id' => $accountId,
                'cost_center_id' => null,
                'reference_type' => Invoice::class,
                'reference_id' => $invoice->id,
                'notes' => 'Auto payment from booking #' . $booking->id,
            ]);
        }
    }

    private function nextInvoiceNumber(int|string $tenantId): string
    {
        $count = (int) Invoice::query()->where('tenant_id', $tenantId)->count();
        $next = $count + 1;
        return 'INV-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function resolveDefaultCashAccountId(int|string $tenantId): int
    {
        $account = Account::query()
            ->where('tenant_id', $tenantId)
            ->where('type', 'asset')
            ->orderBy('id')
            ->first();

        if (! $account) {
            $account = Account::query()->create([
                'tenant_id' => $tenantId,
                'code' => '1000',
                'name' => 'Cash',
                'type' => 'asset',
                'is_active' => true,
            ]);
        }

        return (int) $account->id;
    }
}
