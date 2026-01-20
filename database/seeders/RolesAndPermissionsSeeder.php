<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        $permissions = [
            // Core
            'dashboard.view',

            // Bookings
            'bookings.view',
            'bookings.create',
            'bookings.edit',
            'bookings.delete',

            // Clients
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',

            // Invoices
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',

            // Payments
            'payments.view',
            'payments.create',
            'payments.edit',
            'payments.delete',

            // HR + Payroll
            'hr.view',
            'payroll.view',
            'payroll.manage',

            // Permissions management
            'permissions.manage',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => $guard,
            ]);
        }

        // Roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => $guard]);
        $hr = Role::firstOrCreate(['name' => 'hr', 'guard_name' => $guard]);
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => $guard]);

        // Role permissions
        $all = Permission::where('guard_name', $guard)->get();

        $adminAllowed = $all->filter(function ($p) {
            $name = strtolower($p->name);

            // استثناء أي صلاحيات خاصة بالـ Tenant/Company
            return !str_contains($name, 'tenant')
                && !str_contains($name, 'tenants')
                && !str_contains($name, 'company');
        });

        $admin->syncPermissions($adminAllowed->pluck('name')->all());


        $manager->syncPermissions([
            'dashboard.view',
            'bookings.view',
            'bookings.create',
            'bookings.edit',
            'bookings.delete',
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            'payments.view',
            'payments.create',
            'payments.edit',
            'payments.delete',
            'hr.view',
            'payroll.view',
            'payroll.manage',
            'permissions.manage',
        ]);

        $hr->syncPermissions([
            'dashboard.view',
            'hr.view',
            'payroll.view',
            'payroll.manage',
        ]);

        $staff->syncPermissions([
            'dashboard.view',
            'bookings.view',
            'clients.view',
        ]);
    }
}
