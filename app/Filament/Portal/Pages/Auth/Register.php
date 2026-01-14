<?php

namespace App\Filament\Portal\Pages\Auth;

use App\Models\Tenant;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class Register extends BaseRegister
{
    /**
     * مهم جداً: صفحات Auth في Filament تخزن البيانات تحت "data"
     * وهذا يجعل $data يحتوي company_name بشكل صحيح.
     */
    protected function getFormStatePath(): string
    {
        return 'data';
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('company_name')
                ->label(__('Company name'))
                ->required()
                ->maxLength(255),

            TextInput::make('company_domain')
                ->label(__('Company domain'))
                ->helperText(__('Example: company.example.com (optional in local)'))
                ->maxLength(255),

            TextInput::make('name')
                ->label(__('Admin name'))
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label(__('Email'))
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(User::class, 'email'),

            TextInput::make('password')
                ->label(__('Password'))
                ->password()
                ->required()
                ->rule(Password::default())
                ->same('passwordConfirmation'),

            TextInput::make('passwordConfirmation')
                ->label(__('Confirm password'))
                ->password()
                ->required(),
        ];
    }

    protected function handleRegistration(array $data): User
    {
        $companyName = $data['company_name'] ?? null;

        if (! $companyName) {
            throw ValidationException::withMessages([
                'data.company_name' => __('Company name is required.'),
            ]);
        }

        $tenant = Tenant::create([
            'name'   => $companyName,
            'domain' => $data['company_domain'] ?? null,
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'tenant_id' => $tenant->id,
        ]);

        $role = Role::firstOrCreate(['name' => 'company_admin', 'guard_name' => 'web']);
        $user->assignRole($role);

        return $user;
    }
}
