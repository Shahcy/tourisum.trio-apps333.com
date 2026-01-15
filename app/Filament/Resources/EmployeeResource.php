<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?int $navigationSort = 10;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    // Tenancy
    protected static ?string $tenantOwnershipRelationshipName = 'tenant';
    protected static ?string $tenantRelationshipName = 'employees';

    /**
     * Avoid hardcoded strings so locale switching works properly.
     */
    public static function getNavigationGroup(): ?string
    {
        return __('hr.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('hr.employees.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('hr.employees.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('hr.employees.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('hr.employees.sections.employee_data'))
                ->schema([
                    Forms\Components\TextInput::make('employee_code')
                        ->label(__('hr.employees.fields.employee_code'))
                        ->maxLength(50)
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('full_name')
                        ->label(__('hr.employees.fields.full_name'))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label(__('hr.employees.fields.email'))
                        ->email()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->label(__('hr.employees.fields.phone'))
                        ->maxLength(50),
                ])
                ->columns(2),

            Forms\Components\Section::make(__('hr.employees.sections.job_salary'))
                ->schema([
                    Forms\Components\Select::make('department_id')
                        ->label(__('hr.employees.fields.department'))
                        ->relationship('department', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('job_title_id')
                        ->label(__('hr.employees.fields.job_title'))
                        ->relationship('jobTitle', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\DatePicker::make('hire_date')
                        ->label(__('hr.employees.fields.hire_date')),

                    Forms\Components\TextInput::make('basic_salary')
                        ->label(__('hr.employees.fields.basic_salary'))
                        ->numeric()
                        ->prefix(__('common.currency_egp'))
                        ->minValue(0),
                ])
                ->columns(2),

            Forms\Components\Toggle::make('is_active')
                ->label(__('common.active'))
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_code')
                    ->label(__('hr.employees.columns.code_short'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('hr.employees.columns.name_short'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label(__('hr.employees.fields.department'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('jobTitle.name')
                    ->label(__('hr.employees.fields.job_title'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('common.active'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('hire_date')
                    ->label(__('hr.employees.fields.hire_date'))
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('common.active')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('clock_in')
                    ->label(__('hr.employees.actions.clock_in'))
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $tenantId = Filament::getTenant()?->getKey();
                        $today = now()->toDateString();
                        $nowTime = now()->format('H:i');

                        $attendance = \App\Models\Attendance::query()
                            ->where('tenant_id', $tenantId)
                            ->where('employee_id', $record->id)
                            ->whereDate('date', $today)
                            ->first();

                        if ($attendance && $attendance->check_in) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('hr.employees.notifications.clock_in_already'))
                                ->warning()
                                ->send();
                            return;
                        }

                        if (! $attendance) {
                            $attendance = \App\Models\Attendance::create([
                                'tenant_id' => $tenantId,
                                'employee_id' => $record->id,
                                'date' => $today,
                                'check_in' => $nowTime,
                            ]);
                        } else {
                            $attendance->update(['check_in' => $nowTime]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title(__('hr.employees.notifications.clock_in_done'))
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('clock_out')
                    ->label(__('hr.employees.actions.clock_out'))
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $tenantId = Filament::getTenant()?->getKey();
                        $today = now()->toDateString();
                        $nowTime = now()->format('H:i');

                        $attendance = \App\Models\Attendance::query()
                            ->where('tenant_id', $tenantId)
                            ->where('employee_id', $record->id)
                            ->whereDate('date', $today)
                            ->first();

                        if (! $attendance || ! $attendance->check_in) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('hr.employees.notifications.no_clock_in_today'))
                                ->warning()
                                ->send();
                            return;
                        }

                        if ($attendance->check_out) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('hr.employees.notifications.clock_out_already'))
                                ->warning()
                                ->send();
                            return;
                        }

                        $attendance->update(['check_out' => $nowTime]);

                        \Filament\Notifications\Notification::make()
                            ->title(__('hr.employees.notifications.clock_out_done'))
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) (
            $user?->can('view_any_employee')
            || $user?->hasAnyRole(['admin', 'super_admin'])
        );
    }
}
