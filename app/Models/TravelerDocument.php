<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelerDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_traveler_id',
        'tenant_id',
        'document_type',
        'file_path',
        'visibility',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function traveler()
    {
        return $this->belongsTo(BookingTraveler::class, 'booking_traveler_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}