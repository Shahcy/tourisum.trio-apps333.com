<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'title',
        'description',
        'qty',
        'unit_price',
        'total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($item) {
            $item->total = $item->qty * $item->unit_price;
        });
    }

    public function invoice()
    {
        return $this->belongsTo(\App\Models\Invoice::class);
    }
}
