<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'email',
        'phone',
        'country',
        'currency',
        'logo_path',
        'primary_color',
        'secondary_color',
        'accent_color',
        'is_active',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'tenant_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'tenant_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'tenant_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'tenant_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'tenant_id');
    }

    public function jobTitles(): HasMany
    {
        return $this->hasMany(JobTitle::class, 'tenant_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'tenant_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'tenant_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'tenant_id');
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'tenant_id');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'tenant_id');
    }

    public function costCenters(): HasMany
    {
        return $this->hasMany(CostCenter::class, 'tenant_id');
    }

    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class, 'tenant_id');
    }

    public function integrationLogs(): HasMany
    {
        return $this->hasMany(IntegrationLog::class, 'tenant_id');
    }
}
