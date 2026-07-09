<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | System Roles
        |--------------------------------------------------------------------------
        */

        $roles = [

            'Super Admin',
            'Company Admin',
            'Manager',
            'Sales Executive',
            'HR',
            'Developer',
            'Designer',
            'SEO Executive',
            'Accounts',
            'Support Executive',
            'Client',
            'Employee',

        ];

        foreach ($roles as $role) {

            Role::firstOrCreate(

                [
                    'name' => $role,
                ],

                [
                    'guard_name' => 'web',
                    'company_id' => null,
                    'is_system'  => true,
                ]

            );

        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        Role::findByName('Super Admin')
            ->syncPermissions(
                Permission::all()
            );

        /*
        |--------------------------------------------------------------------------
        | Company Admin
        |--------------------------------------------------------------------------
        */

        Role::findByName('Company Admin')
            ->syncPermissions([

                'companies.view',
                'companies.create',
                'companies.edit',
                'companies.delete',

                'departments.view',
                'departments.create',
                'departments.edit',
                'departments.delete',

                'roles.view',
                'roles.create',
                'roles.edit',
                'roles.delete',

                'users.view',
                'users.create',
                'users.edit',
                'users.delete',

                'users.activate',
                'users.deactivate',

                'users.assign_role',
                'users.assign_permission',

                'activity_logs.view',

                'notifications.view',

            ]);

        /*
        |--------------------------------------------------------------------------
        | Manager
        |--------------------------------------------------------------------------
        */

        Role::findByName('Manager')
            ->syncPermissions([

                'departments.view',

                'users.view',

                'users.profile.view',

                'projects.view',
                'projects.create',
                'projects.edit',

            ]);

        /*
        |--------------------------------------------------------------------------
        | HR
        |--------------------------------------------------------------------------
        */

        Role::findByName('HR')
            ->syncPermissions([

                'users.view',
                'users.create',
                'users.edit',

                'users.activate',
                'users.deactivate',

                'users.profile.view',
                'users.profile.edit',

                'login_history.view',

            ]);

        /*
        |--------------------------------------------------------------------------
        | Developer
        |--------------------------------------------------------------------------
        */

        Role::findByName('Developer')
            ->syncPermissions([

                'departments.view',

                'projects.view',
                'projects.create',
                'projects.edit',

            ]);

        /*
        |--------------------------------------------------------------------------
        | Employee
        |--------------------------------------------------------------------------
        */

        Role::findByName('Employee')
            ->syncPermissions([

                'departments.view',

                'users.profile.view',
                'users.profile.edit',

                'projects.view',

            ]);

        /*
        |--------------------------------------------------------------------------
        | Designer
        |--------------------------------------------------------------------------
        */

        Role::findByName('Designer')
            ->syncPermissions([

                'projects.view',
                'projects.edit',

            ]);

        /*
        |--------------------------------------------------------------------------
        | Sales Executive
        |--------------------------------------------------------------------------
        */

        Role::findByName('Sales Executive')
            ->syncPermissions([

                'clients.view',
                'clients.create',
                'clients.edit',

            ]);

        /*
        |--------------------------------------------------------------------------
        | SEO Executive
        |--------------------------------------------------------------------------
        */

        Role::findByName('SEO Executive')
            ->syncPermissions([

                'projects.view',

            ]);

        /*
        |--------------------------------------------------------------------------
        | Accounts
        |--------------------------------------------------------------------------
        */

        Role::findByName('Accounts')
            ->syncPermissions([

                'invoices.view',
                'invoices.create',
                'invoices.edit',

            ]);

        /*
        |--------------------------------------------------------------------------
        | Support Executive
        |--------------------------------------------------------------------------
        */

        Role::findByName('Support Executive')
            ->syncPermissions([

                'clients.view',
                'projects.view',

            ]);

        /*
        |--------------------------------------------------------------------------
        | Client
        |--------------------------------------------------------------------------
        */

        Role::findByName('Client')
            ->syncPermissions([

                'projects.view',

            ]);
    }
}