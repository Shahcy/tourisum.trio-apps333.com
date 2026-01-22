<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Booking;
use App\Models\BookingActivity;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class BookingObserver
{
    public function creating(Booking $booking): void
    {
        if (! $booking->booking_number) {
            $booking->booking_number = $this->nextBookingNumber($booking->tenant_id);
        }
    }

    public function created(Booking $booking): void
    {
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
            'unit_price' => (float) ($booking->total_amount ?: $booking->grand_total ?: 0),
        ]);

        $invoice->recalculateTotals();

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

        $this->logActivity($booking, 'created', 'Booking created');
    }

    public function updated(Booking $booking): void
    {
        if ($booking->wasChanged('status')) {
            $this->logActivity(
                $booking,
                'status_changed',
                'Status changed to ' . $booking->status
            );
        }
    }

    private function nextBookingNumber(int|string $tenantId): string
    {
        $maxId = (int) Booking::query()
            ->where('tenant_id', $tenantId)
            ->max('id');

        $next = $maxId + 1;
        return 'BKG-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
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

    private function logActivity(Booking $booking, string $action, string $description): void
    {
        BookingActivity::create([
            'booking_id' => $booking->id,
            'tenant_id' => $booking->tenant_id,
            'actor_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
        ]);
    }
}
