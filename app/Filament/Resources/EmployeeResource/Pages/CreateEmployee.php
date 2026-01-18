<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function afterCreate(): void
    {
        $employee = $this->record;

        // إذا كان مربوط مسبقًا بيوزر، اطلع
        if ($employee->user_id) {
            return;
        }

        // إذا ما في إيميل: لا تنشئ يوزر (اليوزر يحتاج unique email غالبًا)
        if (blank($employee->email)) {
            return;
        }

        // إذا الإيميل موجود مسبقًا على users: لا تنشئ، اربطه على الموجود
        $existingUser = User::query()
            ->where('tenant_id', $employee->tenant_id)
            ->where('email', $employee->email)
            ->first();

        if ($existingUser) {
            $employee->update(['user_id' => $existingUser->id]);
            return;
        }

        // أنشئ User بدون Role
        $user = User::create([
            'name'      => $employee->full_name,
            'email'     => $employee->email,
            'password'  => bcrypt(Str::random(20)),
            'tenant_id' => $employee->tenant_id,
        ]);

        $employee->update([
            'user_id' => $user->id,
        ]);
    }
}
