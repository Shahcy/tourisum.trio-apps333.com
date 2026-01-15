<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?int $navigationSort = 20;
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    // Tenancy
    protected static ?string $tenantOwnershipRelationshipName = 'tenant';
    protected static ?string $tenantRelationshipName = 'attendances';

    /**
     * Avoid hardcoded strings so locale switching works properly.
     */
    public static function getNavigationGroup(): ?string
    {
        return __('hr.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('hr.attendance.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('hr.attendance.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('hr.attendance.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('hr.attendance.section.title'))
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->label(__('hr.attendance.fields.employee'))
                        ->relationship('employee', 'full_name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\DatePicker::make('date')
                        ->label(__('hr.attendance.fields.date'))
                        ->default(now()->toDateString())
                        ->required(),

                    Forms\Components\TimePicker::make('check_in')
                        ->label(__('hr.attendance.fields.check_in'))
                        ->seconds(false),

                    Forms\Components\TimePicker::make('check_out')
                        ->label(__('hr.attendance.fields.check_out'))
                        ->seconds(false),

                    Forms\Components\Textarea::make('note')
                        ->label(__('hr.attendance.fields.note'))
                        ->rows(3),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label(__('hr.attendance.fields.employee'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label(__('hr.attendance.fields.date'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('check_in')
                    ->label(__('hr.attendance.fields.check_in_short'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('check_out')
                    ->label(__('hr.attendance.fields.check_out_short'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('note')
                    ->label(__('hr.attendance.fields.note'))
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('today')
                    ->label(__('hr.attendance.filters.today'))
                    ->query(fn(Builder $query) => $query->whereDate('date', now()->toDateString())),

                Tables\Filters\Filter::make('this_month')
                    ->label(__('hr.attendance.filters.this_month'))
                    ->query(fn(Builder $query) => $query->whereBetween('date', [
                        now()->startOfMonth()->toDateString(),
                        now()->endOfMonth()->toDateString(),
                    ])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('date', 'desc');
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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) (
            $user?->can('view_any_attendance')
            || $user?->hasAnyRole(['admin', 'super_admin'])
        );
    }
}
