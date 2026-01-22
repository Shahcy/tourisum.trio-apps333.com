<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'type',
        'booking_number',
        'booking_type',
        'channel',
        'agent_id',
        'branch',
        'priority',
        'tags',
        'source_campaign',
        'reference',
        'destination',
        'start_date',
        'end_date',
        'adults',
        'children',
        'total_amount',
        'paid_amount',
        'subtotal',
        'discount_amount',
        'discount_percent',
        'taxes',
        'service_fees',
        'grand_total',
        'cost_total',
        'profit',
        'profit_margin',
        'currency',
        'exchange_rate',
        'price_locked',
        'payment_status',
        'cancellation_policy',
        'refund_rules',
        'cancellation_fee',
        'refund_amount',
        'status',
        'voucher_path',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'tags' => 'array',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'taxes' => 'decimal:2',
        'service_fees' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'cost_total' => 'decimal:2',
        'profit' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'price_locked' => 'boolean',
        'cancellation_fee' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function contacts()
    {
        return $this->hasMany(BookingContact::class);
    }

    public function travelers()
    {
        return $this->hasMany(BookingTraveler::class);
    }

    public function travelerDocuments()
    {
        return $this->hasManyThrough(
            TravelerDocument::class,
            BookingTraveler::class,
            'booking_id',
            'booking_traveler_id'
        );
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payments()
    {
        return $this->hasMany(BookingPayment::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function documents()
    {
        return $this->hasMany(BookingDocument::class);
    }

    public function tasks()
    {
        return $this->hasMany(BookingTask::class);
    }

    public function notes()
    {
        return $this->hasMany(BookingNote::class);
    }

    public function activities()
    {
        return $this->hasMany(BookingActivity::class);
    }

    public function bookingInvoices()
    {
        return $this->hasMany(BookingInvoice::class);
    }
}
