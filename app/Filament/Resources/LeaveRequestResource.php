<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationGroup = 'HR';
    protected static ?int $navigationSort = 30;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Leave Requests';

    // Tenancy (عشان ما يعتمد على التخمين)
    protected static ?string $tenantOwnershipRelationshipName = 'tenant';
    protected static ?string $tenantRelationshipName = 'leaveRequests';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('employee_id')
                ->label('الموظف')
                ->relationship('employee', 'full_name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\DatePicker::make('start_date')
                ->label('من تاريخ')
                ->required(),

            Forms\Components\DatePicker::make('end_date')
                ->label('إلى تاريخ')
                ->required(),

            Forms\Components\Select::make('type')
                ->label('نوع الإجازة')
                ->options([
                    'annual' => 'سنوية',
                    'sick' => 'مرضية',
                    'unpaid' => 'بدون راتب',
                    'other' => 'أخرى',
                ])
                ->required(),

            Forms\Components\Select::make('status')
                ->label('الحالة')
                ->options([
                    'pending' => 'معلّقة',
                    'approved' => 'مقبولة',
                    'rejected' => 'مرفوضة',
                ])
                ->default('pending')
                ->required(),

            Forms\Components\Textarea::make('reason')
                ->label('السبب / ملاحظات')
                ->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('الموظف')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'annual' => 'سنوية',
                        'sick' => 'مرضية',
                        'unpaid' => 'بدون راتب',
                        default => 'أخرى',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'pending' => 'معلّقة',
                        'approved' => 'مقبولة',
                        default => 'مرفوضة',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('من')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('إلى')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reason')
                    ->label('ملاحظات')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('موافقة')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(function (LeaveRequest $record) {
                        /** @var \App\Models\User|null $user */
                        $user = \Illuminate\Support\Facades\Auth::user();

                        return $record->status === 'pending'
                            && (bool) (
                                $user?->can('hr.manage')
                                || $user?->hasAnyRole(['admin', 'super_admin'])
                            );
                    })
                    ->requiresConfirmation()
                    ->action(fn(LeaveRequest $record) => $record->update(['status' => 'approved'])),

                Tables\Actions\Action::make('reject')
                    ->label('رفض')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(function (LeaveRequest $record) {
                        /** @var \App\Models\User|null $user */
                        $user = \Illuminate\Support\Facades\Auth::user();

                        return $record->status === 'pending'
                            && (bool) (
                                $user?->can('hr.manage')
                                || $user?->hasAnyRole(['admin', 'super_admin'])
                            );
                    })
                    ->form([
                        Forms\Components\Textarea::make('reject_reason')
                            ->label('سبب الرفض (اختياري)')
                            ->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->action(function (LeaveRequest $record, array $data) {
                        $newReason = $record->reason;

                        if (! empty($data['reject_reason'])) {
                            $newReason = trim(($record->reason ? $record->reason . "\n\n" : '') . 'سبب الرفض: ' . $data['reject_reason']);
                        }

                        $record->update([
                            'status' => 'rejected',
                            'reason' => $newReason,
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }


    public static function getEloquentQuery(): Builder
    {
        // للتأكيد: حصر النتائج على Tenant الحالي حتى لو تغيّرت إعدادات panel
        $tenantId = Filament::getTenant()?->getKey();

        return parent::getEloquentQuery()
            ->when($tenantId, fn(Builder $q) => $q->where('tenant_id', $tenantId));
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = Filament::getTenant()?->getKey();
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['tenant_id'] = Filament::getTenant()?->getKey();
        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'edit' => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }
    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) (
            $user?->can('view_any_leave::request')
            || $user?->hasAnyRole(['admin', 'super_admin'])
        );
    }
}
