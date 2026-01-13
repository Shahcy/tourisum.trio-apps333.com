<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tenant;

class JournalEntry extends Model
{
    protected $fillable = [
        'tenant_id',
        'entry_no',
        'date',
        'description',
        'status',
        'posted_at',
        'posted_by',
    ];

    protected $casts = [
        'date' => 'date',
        'posted_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function totals(): array
    {
        $debit = (float) $this->lines()->sum('debit');
        $credit = (float) $this->lines()->sum('credit');

        return [
            'debit' => $debit,
            'credit' => $credit,
            'balanced' => abs($debit - $credit) < 0.01,
        ];
    }

    public function post(int $userId): void
    {
        if ($this->status === 'posted') {
            return;
        }

        $totals = $this->totals();

        if (!$totals['balanced'] || $totals['debit'] <= 0) {
            throw ValidationException::withMessages([
                'lines' => 'لا يمكن ترحيل القيد: مجموع المدين يجب أن يساوي مجموع الدائن وأن يكون أكبر من صفر.',
            ]);
        }

        DB::transaction(function () use ($userId) {
            $this->refresh();

            if ($this->status === 'posted') {
                return;
            }

            $this->update([
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => $userId,
            ]);
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
