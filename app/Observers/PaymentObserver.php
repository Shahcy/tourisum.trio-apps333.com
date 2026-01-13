<?php

namespace App\Observers;

use App\Models\InvoiceItem;

class InvoiceItemObserver
{
    public function saved(InvoiceItem $item): void
    {
        $this->recalculateInvoice($item);
    }

    public function deleted(InvoiceItem $item): void
    {
        $this->recalculateInvoice($item);
    }

    private function recalculateInvoice(InvoiceItem $item): void
    {
        $invoice = $item->invoice;

        if (! $invoice) {
            return;
        }

        $invoice->loadMissing('items');

        $subtotal = (float) $invoice->items->sum(function ($i) {
            return ((float) $i->qty) * ((float) $i->unit_price);
        });

        $discount = (float) ($invoice->discount ?? 0);
        $tax      = (float) ($invoice->tax ?? 0);

        $total = max($subtotal - $discount + $tax, 0);

        $invoice->forceFill([
            'subtotal' => $subtotal,
            'total'    => $total,
        ])->saveQuietly(); // مهم: حتى ما يعمل loop
    }
}
