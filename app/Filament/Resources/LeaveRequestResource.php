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

    protected static ?int $navigationSort = 30;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Tenancy
    protected static ?string $tenantOwnershipRelationshipName = 'tenant';
    protected static ?string $tenantRelationshipName = 'leaveRequests';

    /**
     * Avoid hardcoded strings so locale switching works properly.
     */
    public static function getNavigationGroup(): ?string
    {
        return __('hr.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('hr.leave_requests.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('hr.leave_requests.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('hr.leave_requests.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('employee_id')
                ->label(__('hr.leave_requests.fields.employee'))
                ->relationship('employee', 'full_name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\DatePicker::make('start_date')
                ->label(__('hr.leave_requests.fields.start_date'))
                ->required(),

            Forms\Components\DatePicker::make('end_date')
                ->label(__('hr.leave_requests.fields.end_date'))
                ->required(),

            Forms\Components\Select::make('type')
                ->label(__('hr.leave_requests.fields.type'))
                ->options([
                    'annual' => __('hr.leave_requests.types.annual'),
                    'sick'   => __('hr.leave_requests.types.sick'),
                    'unpaid' => __('hr.leave_requests.types.unpaid'),
                    'other'  => __('hr.leave_requests.types.other'),
                ])
                ->required(),

            Forms\Components\Select::make('status')
                ->label(__('hr.leave_requests.fields.status'))
                ->options([
                    'pending'  => __('hr.leave_requests.statuses.pending'),
                    'approved' => __('hr.leave_requests.statuses.approved'),
                    'rejected' => __('hr.leave_requests.statuses.rejected'),
                ])
                ->default('pending')
                ->required(),

            Forms\Components\Textarea::make('reason')
                ->label(__('hr.leave_requests.fields.reason'))
                ->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label(__('hr.leave_requests.fields.employee'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('hr.leave_requests.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'annual' => __('hr.leave_requests.types.annual'),
                        'sick'   => __('hr.leave_requests.types.sick'),
                        'unpaid' => __('hr.leave_requests.types.unpaid'),
                        'other'  => __('hr.leave_requests.types.other'),
                        default  => (string) $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('hr.leave_requests.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'pending'  => __('hr.leave_requests.statuses.pending'),
                        'approved' => __('hr.leave_requests.statuses.approved'),
                        'rejected' => __('hr.leave_requests.statuses.rejected'),
                        default    => (string) $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('hr.leave_requests.columns.from'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label(__('hr.leave_requests.columns.to'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reason')
                    ->label(__('hr.leave_requests.fields.notes_short'))
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label(__('hr.leave_requests.actions.approve'))
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(function (LeaveRequest $record) {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return $record->status === 'pending'
                            && (bool) (
                                $user?->can('hr.manage')
                                || $user?->hasAnyRole(['admin', 'super_admin'])
                            );
                    })
                    ->requiresConfirmation()
                    ->action(fn(LeaveRequest $record) => $record->update(['status' => 'approved'])),

                Tables\Actions\Action::make('reject')
                    ->label(__('hr.leave_requests.actions.reject'))
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(function (LeaveRequest $record) {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return $record->status === 'pending'
                            && (bool) (
                                $user?->can('hr.manage')
                                || $user?->hasAnyRole(['admin', 'super_admin'])
                            );
                    })
                    ->form([
                        Forms\Components\Textarea::make('reject_reason')
                            ->label(__('hr.leave_requests.fields.reject_reason'))
                            ->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->action(function (LeaveRequest $record, array $data) {
                        $newReason = $record->reason;

                        if (! empty($data['reject_reason'])) {
                            $newReason = trim(
                                ($record->reason ? $record->reason . "\n\n" : '')
                                    . __('hr.leave_requests.text.reject_reason_prefix')
                                    . ' '
                                    . $data['reject_reason']
                            );
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
