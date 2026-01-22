<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'tenant_id',
        'note_type',
        'channel',
        'content',
        'next_step',
        'follow_up_at',
    ];

    protected $casts = [
        'follow_up_at' => 'datetime',
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