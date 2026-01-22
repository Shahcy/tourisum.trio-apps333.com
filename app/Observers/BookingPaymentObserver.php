<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\BookingActivity;
use App\Models\BookingPayment;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class BookingPaymentObserver
{
    public function created(BookingPayment $payment): void
    {
        $booking = $payment->booking;
        $invoice = $booking?->invoice
            ?? Invoice::query()->where('booking_id', $payment->booking_id)->first();

        if ($invoice) {
            $accountId = $this->resolveDefaultCashAccountId($payment->tenant_id);
            $date = ($payment->received_at ?? now())->toDateString();

            Payment::create([
                'tenant_id' => $payment->tenant_id,
                'direction' => $payment->is_refund ? 'out' : 'in',
                'date' => $date,
                'amount' => $payment->amount,
                'method' => $payment->method,
                'account_id' => $accountId,
                'cost_center_id' => null,
                'reference_type' => Invoice::class,
                'reference_id' => $invoice->id,
                'notes' => 'Booking payment #' . $payment->id,
            ]);
        }

        BookingActivity::create([
            'booking_id' => $payment->booking_id,
            'tenant_id' => $payment->tenant_id,
            'actor_id' => Auth::id(),
            'action' => $payment->is_refund ? 'refund_created' : 'payment_received',
            'description' => sprintf('Payment %s: %.2f', $payment->is_refund ? 'refund' : 'received', $payment->amount),
        ]);
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
