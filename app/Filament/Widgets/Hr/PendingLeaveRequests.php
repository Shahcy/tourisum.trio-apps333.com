<?php

namespace App\Filament\Widgets\Hr;

use App\Models\LeaveRequest;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PendingLeaveRequests extends BaseWidget
{
    protected static bool $isLazy = true;

    protected function getHeading(): ?string
    {
        return 'طلبات الإجازة (معلّقة)';
    }

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $tenantId = Filament::getTenant()?->getKey();

        return LeaveRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('employee.full_name')
                ->label('الموظف')
                ->searchable(),

            Tables\Columns\TextColumn::make('type')
                ->label('النوع')
                ->badge(),

            Tables\Columns\TextColumn::make('start_date')
                ->label('من')
                ->date(),

            Tables\Columns\TextColumn::make('end_date')
                ->label('إلى')
                ->date(),

            Tables\Columns\TextColumn::make('reason')
                ->label('السبب')
                ->limit(40)
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('approve')
                ->label('موافقة')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->action(function (LeaveRequest $record) {
                    if ($record->status !== 'pending') {
                        Notification::make()->title('الطلب ليس معلّقًا')->warning()->send();
                        return;
                    }

                    $record->update(['status' => 'approved']);

                    Notification::make()->title('تمت الموافقة على الإجازة')->success()->send();
                }),

            Tables\Actions\Action::make('reject')
                ->label('رفض')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->form([
                    Forms\Components\Textarea::make('reject_reason')
                        ->label('سبب الرفض (اختياري)')
                        ->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (LeaveRequest $record, array $data) {
                    if ($record->status !== 'pending') {
                        Notification::make()->title('الطلب ليس معلّقًا')->warning()->send();
                        return;
                    }

                    // إذا بدك تخزن سبب الرفض في نفس حقل reason (بدون إضافة عمود جديد)
                    $newReason = $record->reason;

                    if (!empty($data['reject_reason'])) {
                        $newReason = trim(($record->reason ? $record->reason . "\n\n" : '') . 'سبب الرفض: ' . $data['reject_reason']);
                    }

                    $record->update([
                        'status' => 'rejected',
                        'reason' => $newReason,
                    ]);

                    Notification::make()->title('تم رفض الإجازة')->success()->send();
                }),
        ];
    }

    protected function getDefaultTableRecordsPerPage(): int
    {
        return 5;
    }
    public static function canView(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) (
            Gate::forUser($user)->check('widget_PendingLeaveRequests')
            || $user?->hasAnyRole(['admin', 'super_admin'])
        );
    }
}
