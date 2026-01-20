<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
        static::creating(function (self $payment) {
            if (empty($payment->direction)) {
                $payment->direction = 'in';
            }

            // tenant_id لازم يجي من الفورم/السيرفس/الـ scoping
            // ما بنعتمد على Filament هون لأنه طبقة UI
        });
    }
}
