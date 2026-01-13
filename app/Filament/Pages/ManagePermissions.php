<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ManagePermissions extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'إدارة الصلاحيات';
    protected static ?string $navigationGroup = 'الإعدادات';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-permissions';

    public ?array $data = [
        'role' => null,
        'permissions' => [],
    ];

    public function mount(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        abort_unless($user && $user->hasAnyRole(['admin', 'manager']), 403);

        $this->data['role'] = Role::query()->orderBy('name')->value('name');
        $this->loadRolePermissions();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Select::make('role')
                    ->label('اختر الدور')
                    ->options(Role::query()->orderBy('name')->pluck('name', 'name')->toArray())
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (): void {
                        $this->loadRolePermissions();
                    }),

                // أزرار Select All / Deselect All
                Actions::make([
                    Action::make('selectAll')
                        ->label('تحديد الكل')
                        ->action(function (): void {
                            $this->data['permissions'] = Permission::query()
                                ->where('guard_name', 'web')
                                ->orderBy('name')
                                ->pluck('name')
                                ->values()
                                ->all();

                            $this->form->fill($this->data);
                        }),

                    Action::make('deselectAll')
                        ->label('إلغاء تحديد الكل')
                        ->color('gray')
                        ->action(function (): void {
                            $this->data['permissions'] = [];
                            $this->form->fill($this->data);
                        }),
                ])->columnSpanFull(),

                CheckboxList::make('permissions')
                    ->label('الصلاحيات')
                    ->options(
                        Permission::query()
                            ->where('guard_name', 'web')
                            ->orderBy('name')
                            ->pluck('name', 'name')
                            ->toArray()
                    )
                    ->columns(2)
                    ->searchable(),
            ]);
    }

    private function loadRolePermissions(): void
    {
        $roleName = $this->data['role'] ?? null;

        if (! $roleName) {
            $this->data['permissions'] = [];
            $this->form->fill($this->data);
            return;
        }

        $role = Role::query()
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->first();

        if (! $role) {
            $this->data['permissions'] = [];
            $this->form->fill($this->data);
            return;
        }

        $this->data['permissions'] = $role->permissions()
            ->pluck('name')
            ->values()
            ->all();

        $this->form->fill($this->data);
    }

    public function save(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        abort_unless($user && $user->hasAnyRole(['admin', 'manager']), 403);

        $state = $this->form->getState();

        $roleName = $state['role'] ?? null;
        abort_unless($roleName, 422);

        $selected = $state['permissions'] ?? [];
        if (! is_array($selected)) {
            $selected = [];
        }

        $role = Role::findByName($roleName, 'web');
        $role->syncPermissions($selected);

        Notification::make()
            ->title('تم حفظ الصلاحيات بنجاح')
            ->success()
            ->send();

        $this->loadRolePermissions();
    }
}
