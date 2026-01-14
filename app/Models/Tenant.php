<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Department;
use App\Models\JobTitle;


class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'country',
        'currency',
        'logo_path',
        'primary_color',
        'is_active',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // العلاقات التي يحتاجها النظام الحالي عندك
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // علاقات HR المطلوبة لـ Filament Tenancy
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function jobTitles(): HasMany
    {
        return $this->hasMany(JobTitle::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(\App\Models\LeaveRequest::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(\App\Models\Employee::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(\App\Models\Attendance::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(\App\Models\Account::class, 'tenant_id');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function costCenters(): HasMany
    {
        return $this->hasMany(\App\Models\CostCenter::class);
    }
}
