<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InvoicePayment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'booking_id',
        'number',
        'issue_date',
        'due_date',
        'subtotal',
        'discount',
        'tax',
        'total',
        'status',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(\App\Models\Payment::class, 'reference');
    }


    public function getPaidAmountAttribute()
    {
        return (float) $this->payments()
            ->where('direction', 'in')
            ->sum('amount');
    }

    public function getRemainingAttribute()
    {
        return max((float) $this->total - (float) $this->paid_amount, 0);
    }

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items()
            ->selectRaw('COALESCE(SUM(qty * unit_price), 0) as subtotal')
            ->value('subtotal');

        $discount = (float) ($this->discount ?? 0);
        $tax      = (float) ($this->tax ?? 0);

        $total = max($subtotal - $discount + $tax, 0);

        $this->forceFill([
            'subtotal' => $subtotal,
            'total'    => $total,
        ])->saveQuietly(); // مهم عشان ما يعمل loop
    }
}
