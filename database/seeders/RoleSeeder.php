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
        'roles.assign_permissions',
        'roles.view_permissions',

        'users.view',
        'users.create',
        'users.edit',
        'users.delete',

        'users.activate',
        'users.deactivate',
        'users.restore',

        'users.import',
        'users.export',

        'users.assign_role',
        'users.assign_permission',

        'activity_logs.view',

        'notifications.view',
        'login-history.view',
        'ai.commands.view',
        'ai.commands.execute',

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
                'notifications.view',

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
                'notifications.view',

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

                'projects.view',
                'projects.create',
                'notifications.view',
                'projects.edit',

            ]);

        /*
        |--------------------------------------------------------------------------
        | Employee
        |--------------------------------------------------------------------------
        */

        Role::findByName('Employee')
            ->syncPermissions([

                'users.profile.view',
                'users.profile.edit',
                'notifications.view',
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
                'notifications.view',

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
                'notifications.view',

            ]);

        /*
        |--------------------------------------------------------------------------
        | SEO Executive
        |--------------------------------------------------------------------------
        */

        Role::findByName('SEO Executive')
            ->syncPermissions([

                'projects.view',
                'notifications.view',

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
                'notifications.view',

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
                'notifications.view',

            ]);

        /*
        |--------------------------------------------------------------------------
        | Client
        |--------------------------------------------------------------------------
        */

        Role::findByName('Client')
            ->syncPermissions([

                'projects.view',
                'notifications.view',

            ]);
    }
}