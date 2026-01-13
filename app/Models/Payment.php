<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Tenant;
use Filament\Facades\Filament;

class Payment extends Model
{
    protected $fillable = [
        'tenant_id',
        'direction',
        'date',
        'amount',
        'method',
        'account_id',
        'cost_center_id',
        'reference_type',
        'reference_id',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function booted(): void
    {
        static::creating(function ($payment) {
            // لو ما انبعت direction من الفورم
            if (empty($payment->direction)) {
                // إذا الدفعة مرتبطة بفاتورة -> قبض
                if ($payment->reference_type === \App\Models\Invoice::class) {
                    $payment->direction = 'in';
                } else {
                    $payment->direction = 'in'; // default عام
                }
            }

            // تأكد tenant_id إذا ناقص
            if (empty($payment->tenant_id)) {
                $payment->tenant_id = Filament::getTenant()?->getKey();
            }
        });
    }
}
