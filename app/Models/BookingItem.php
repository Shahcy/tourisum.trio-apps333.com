<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'tenant_id',
        'item_type',
        'supplier_name',
        'supplier_contact',
        'start_date',
        'end_date',
        'cost_amount',
        'price_amount',
        'taxes',
        'fees',
        'status',
        'reference',
        'notes',
        'quantity',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'cost_amount' => 'decimal:2',
        'price_amount' => 'decimal:2',
        'taxes' => 'decimal:2',
        'fees' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}