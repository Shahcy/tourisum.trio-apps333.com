<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'tenant_id',
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
