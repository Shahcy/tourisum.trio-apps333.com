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

    protected static ?string $navigationGroup = 'HR';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'Employees';

    // Tenancy
    protected static ?string $tenantOwnershipRelationshipName = 'tenant';
    protected static ?string $tenantRelationshipName = 'employees';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات الموظف')
                ->schema([
                    Forms\Components\TextInput::make('employee_code')
                        ->label('الرقم الوظيفي')
                        ->maxLength(50)
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('full_name')
                        ->label('الاسم الكامل')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->email()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->label('رقم الهاتف')
                        ->maxLength(50),
                ])
                ->columns(2),

            Forms\Components\Section::make('الوظيفة والراتب')
                ->schema([
                    Forms\Components\Select::make('department_id')
                        ->label('القسم')
                        ->relationship('department', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('job_title_id')
                        ->label('المسمى الوظيفي')
                        ->relationship('jobTitle', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\DatePicker::make('hire_date')
                        ->label('تاريخ التوظيف'),

                    Forms\Components\TextInput::make('basic_salary')
                        ->label('الراتب الأساسي')
                        ->numeric()
                        ->prefix('EGP')
                        ->minValue(0),
                ])
                ->columns(2),

            Forms\Components\Toggle::make('is_active')
                ->label('فعال')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_code')
                    ->label('رقم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('القسم')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('jobTitle.name')
                    ->label('المسمى')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                Tables\Columns\TextColumn::make('hire_date')
                    ->label('تاريخ التوظيف')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('أُنشئ')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('فعال'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('clock_in')
                    ->label('Clock In')
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
                                ->title('تم تسجيل الدخول مسبقًا اليوم')
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
                            ->title('تم تسجيل Clock In')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('clock_out')
                    ->label('Clock Out')
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
                                ->title('لا يوجد Clock In لهذا اليوم')
                                ->warning()
                                ->send();
                            return;
                        }

                        if ($attendance->check_out) {
                            \Filament\Notifications\Notification::make()
                                ->title('تم تسجيل الخروج مسبقًا اليوم')
                                ->warning()
                                ->send();
                            return;
                        }

                        $attendance->update(['check_out' => $nowTime]);

                        \Filament\Notifications\Notification::make()
                            ->title('تم تسجيل Clock Out')
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
