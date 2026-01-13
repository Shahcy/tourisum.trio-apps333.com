<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'full_name',
        'phone',
        'email',
        'nationality',
        'passport_number',
        'passport_expiry',
        'visa_type',
        'visa_expiry',
        'status',
        'notes',
    ];

    protected $casts = [
        'passport_expiry' => 'date',
        'visa_expiry' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
