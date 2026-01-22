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
        'document_type',
        'document_number',
        'document_expiry',
        'date_of_birth',
        'gender',
        'address',
        'company_name',
        'alt_phone',
        'alt_email',
    ];

    protected $casts = [
        'passport_expiry' => 'date',
        'visa_expiry' => 'date',
        'document_expiry' => 'date',
        'date_of_birth' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
