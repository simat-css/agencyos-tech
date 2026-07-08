<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Create Roles
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        $companyAdmin = Role::firstOrCreate([
            'name' => 'Company Admin',
            'guard_name' => 'web',
        ]);

        $manager = Role::firstOrCreate([
            'name' => 'Manager',
            'guard_name' => 'web',
        ]);

        $hr = Role::firstOrCreate([
            'name' => 'HR',
            'guard_name' => 'web',
        ]);

        $developer = Role::firstOrCreate([
            'name' => 'Developer',
            'guard_name' => 'web',
        ]);

        $designer = Role::firstOrCreate([
            'name' => 'Designer',
            'guard_name' => 'web',
        ]);

        $sales = Role::firstOrCreate([
            'name' => 'Sales',
            'guard_name' => 'web',
        ]);

        $accounts = Role::firstOrCreate([
            'name' => 'Accounts',
            'guard_name' => 'web',
        ]);

        $support = Role::firstOrCreate([
            'name' => 'Support',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Assign Permissions
        |--------------------------------------------------------------------------
        */

        // Super Admin
        $superAdmin->syncPermissions(
            Permission::all()
        );

        // Company Admin
        $companyAdmin->syncPermissions([
            'companies.view',
            'companies.edit',

            'departments.view',
            'departments.create',
            'departments.edit',
            'departments.delete',

            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
        ]);

        // Manager
        $manager->syncPermissions([
            'departments.view',
            'users.view',
        ]);

        // HR
        $hr->syncPermissions([
            'users.view',
            'users.create',
            'users.edit',
        ]);

        // Developer
        $developer->syncPermissions([
            'departments.view',
        ]);

        // Designer
        $designer->syncPermissions([
            'departments.view',
        ]);

        // Sales
        $sales->syncPermissions([]);

        // Accounts
        $accounts->syncPermissions([]);

        // Support
        $support->syncPermissions([]);
    }
}