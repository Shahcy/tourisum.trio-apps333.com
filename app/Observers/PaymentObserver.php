<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            // محاسبة خفيفة: فعّلها/أغلقها لاحقًا بسهولة
            if (config('accounting.light_journals', true)) {
                $this->createLightJournalEntry($payment);
            }

            $this->syncInvoiceStatusIfNeeded($payment);
        });
    }

    public function updated(Payment $payment): void
    {
        $this->syncInvoiceStatusIfNeeded($payment);
    }

    public function deleted(Payment $payment): void
    {
        $this->syncInvoiceStatusIfNeeded($payment);
    }

    private function syncInvoiceStatusIfNeeded(Payment $payment): void
    {
        if ($payment->reference_type !== Invoice::class || empty($payment->reference_id)) {
            return;
        }

        /** @var Invoice|null $invoice */
        $invoice = Invoice::query()->find($payment->reference_id);
        if (! $invoice) {
            return;
        }

        $paid = (float) $invoice->paid_amount;
        $total = (float) $invoice->total;

        if ($paid <= 0) {
            $invoice->updateQuietly(['status' => 'unpaid']);
            return;
        }

        if ($paid + 0.0001 < $total) {
            $invoice->updateQuietly(['status' => 'partial']);
            return;
        }

        $invoice->updateQuietly(['status' => 'paid']);
    }

    private function createLightJournalEntry(Payment $payment): void
    {
        $tenantId = (int) $payment->tenant_id;
        if (! $tenantId) {
            return;
        }

        $cashOrBank = Account::query()
            ->where('tenant_id', $tenantId)
            ->where('id', $payment->account_id)
            ->first();

        if (! $cashOrBank) {
            $cashOrBank = $this->firstOrCreateDefaultCashAccount($tenantId);
        }

        $amount = (float) $payment->amount;
        if ($amount <= 0) {
            return;
        }

        $entryNo = $this->nextEntryNo($tenantId);

        $entry = JournalEntry::create([
            'tenant_id' => $tenantId,
            'entry_no' => $entryNo,
            'date' => $payment->date,
            'description' => $this->journalDescription($payment),
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => Auth::id(),
        ]);

        if ($payment->direction === 'out') {
            $expense = $this->firstOrCreateDefaultExpenseAccount($tenantId);

            $entry->lines()->create([
                'account_id' => $expense->id,
                'cost_center_id' => $payment->cost_center_id,
                'debit' => $amount,
                'credit' => 0,
                'memo' => 'Expense',
            ]);

            $entry->lines()->create([
                'account_id' => $cashOrBank->id,
                'cost_center_id' => $payment->cost_center_id,
                'debit' => 0,
                'credit' => $amount,
                'memo' => 'Cash/Bank',
            ]);

            return;
        }

        $revenue = $this->firstOrCreateDefaultRevenueAccount($tenantId);

        $entry->lines()->create([
            'account_id' => $cashOrBank->id,
            'cost_center_id' => $payment->cost_center_id,
            'debit' => $amount,
            'credit' => 0,
            'memo' => 'Cash/Bank',
        ]);

        $entry->lines()->create([
            'account_id' => $revenue->id,
            'cost_center_id' => $payment->cost_center_id,
            'debit' => 0,
            'credit' => $amount,
            'memo' => 'Revenue',
        ]);
    }

    private function nextEntryNo(int $tenantId): string
    {
        $count = (int) JournalEntry::query()->where('tenant_id', $tenantId)->count();
        $next = $count + 1;
        return 'JE-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function journalDescription(Payment $payment): string
    {
        $base = $payment->direction === 'out' ? 'Payment OUT' : 'Payment IN';

        if ($payment->reference_type === Invoice::class && $payment->reference_id) {
            return $base . ' for Invoice #' . $payment->reference_id;
        }

        return $base;
    }

    private function firstOrCreateDefaultCashAccount(int $tenantId): Account
    {
        return Account::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => '1000'],
            ['name' => 'Cash', 'type' => 'asset', 'is_active' => true],
        );
    }

    private function firstOrCreateDefaultRevenueAccount(int $tenantId): Account
    {
        return Account::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => '4000'],
            ['name' => 'Sales Revenue', 'type' => 'revenue', 'is_active' => true],
        );
    }

    private function firstOrCreateDefaultExpenseAccount(int $tenantId): Account
    {
        return Account::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => '5000'],
            ['name' => 'General Expense', 'type' => 'expense', 'is_active' => true],
        );
    }
}
