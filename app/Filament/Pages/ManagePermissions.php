<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\TranslatesPageAttributes;
use Filament\Forms\Form;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ManagePermissions extends Page
{
    use TranslatesPageAttributes;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Manage Permissions';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $title = 'Manage Permissions';
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
                    ->label('Select role')
                    ->options(Role::query()->orderBy('name')->pluck('name', 'name')->toArray())
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (): void {
                        $this->loadRolePermissions();
                    }),

                // Select All / Deselect All
                Actions::make([
                    Action::make('selectAll')
                        ->label('Select all')
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
                        ->label('Deselect all')
                        ->color('gray')
                        ->action(function (): void {
                            $this->data['permissions'] = [];
                            $this->form->fill($this->data);
                        }),
                ])->columnSpanFull(),

                CheckboxList::make('permissions')
                    ->label('Permissions')
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
            ->title('Permissions updated successfully')
            ->success()
            ->send();

        $this->loadRolePermissions();
    }
}
