<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\Attendance;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('clock_in')
                ->label('Clock In')
                ->color('success')
                ->icon('heroicon-o-play')
                ->requiresConfirmation()
                ->action(function () {
                    $tenantId = Filament::getTenant()?->getKey();
                    $today = now()->toDateString();
                    $nowTime = now()->format('H:i');

                    $employee = $this->record;

                    $attendance = Attendance::query()
                        ->where('tenant_id', $tenantId)
                        ->where('employee_id', $employee->id)
                        ->whereDate('date', $today)
                        ->first();

                    if ($attendance && $attendance->check_in) {
                        Notification::make()->title('تم تسجيل الدخول مسبقًا اليوم')->warning()->send();
                        return;
                    }

                    if (! $attendance) {
                        Attendance::create([
                            'tenant_id' => $tenantId,
                            'employee_id' => $employee->id,
                            'date' => $today,
                            'check_in' => $nowTime,
                        ]);
                    } else {
                        $attendance->update(['check_in' => $nowTime]);
                    }

                    Notification::make()->title('تم تسجيل Clock In')->success()->send();
                }),

            Actions\Action::make('clock_out')
                ->label('Clock Out')
                ->color('danger')
                ->icon('heroicon-o-stop')
                ->requiresConfirmation()
                ->action(function () {
                    $tenantId = Filament::getTenant()?->getKey();
                    $today = now()->toDateString();
                    $nowTime = now()->format('H:i');

                    $employee = $this->record;

                    $attendance = Attendance::query()
                        ->where('tenant_id', $tenantId)
                        ->where('employee_id', $employee->id)
                        ->whereDate('date', $today)
                        ->first();

                    if (! $attendance || ! $attendance->check_in) {
                        Notification::make()->title('لا يوجد Clock In لهذا اليوم')->warning()->send();
                        return;
                    }

                    if ($attendance->check_out) {
                        Notification::make()->title('تم تسجيل الخروج مسبقًا اليوم')->warning()->send();
                        return;
                    }

                    $attendance->update(['check_out' => $nowTime]);

                    Notification::make()->title('تم تسجيل Clock Out')->success()->send();
                }),

            Actions\EditAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\Hr\EmployeeRecentAttendances::make([
                'employeeId' => $this->record->id,
            ]),
            \App\Filament\Widgets\Hr\EmployeeRecentLeaves::make([
                'employeeId' => $this->record->id,
            ]),
        ];
    }
}
