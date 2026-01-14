<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),

            Actions\Action::make('createCompanyAdmin')
                ->label('Create Company Admin')
                ->icon('heroicon-o-user-plus')
                ->form([
                    TextInput::make('name')
                        ->label('Admin name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Admin email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(User::class, 'email'),

                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->required()
                        ->minLength(8),
                ])
                ->action(function (array $data) {
                    $tenant = $this->record;

                    $user = User::create([
                        'name'      => $data['name'],
                        'email'     => $data['email'],
                        'password'  => Hash::make($data['password']),
                        'tenant_id' => $tenant->id,
                    ]);

                    $role = Role::firstOrCreate(['name' => 'company_admin', 'guard_name' => 'web']);
                    $user->assignRole($role);

                    Notification::make()
                        ->title('Company Admin created successfully')
                        ->success()
                        ->send();
                }),
        ];
    }
}
