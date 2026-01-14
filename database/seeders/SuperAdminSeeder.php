<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Role for super admin
        $role = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        // Create or update super admin user (email does NOT need to be real)
        $user = User::updateOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
                // super admin is not tied to a tenant
                'tenant_id' => null,
            ]
        );

        if (! $user->hasRole($role->name)) {
            $user->assignRole($role);
        }
    }
}
