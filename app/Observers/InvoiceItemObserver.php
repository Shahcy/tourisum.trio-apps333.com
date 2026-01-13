<?php

namespace App\Observers;

use App\Models\InvoiceItem;

class InvoiceItemObserver
{
    public function saved(InvoiceItem $item): void
    {
        $item->invoice?->recalculateTotals();
    }

    public function deleted(InvoiceItem $item): void
    {
        $item->invoice?->recalculateTotals();
    }
}
