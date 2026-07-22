<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            /*
            |--------------------------------------------------------------------------
            | Company Management
            |--------------------------------------------------------------------------
            */

            'companies.view',
            'companies.create',
            'companies.edit',
            'companies.delete',

            /*
            |--------------------------------------------------------------------------
            | Department Management
            |--------------------------------------------------------------------------
            */

            'departments.view',
            'departments.create',
            'departments.edit',
            'departments.delete',

            /*
            |--------------------------------------------------------------------------
            | Role Management
            |--------------------------------------------------------------------------
            */

            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'roles.assign_permissions',
            'roles.view_permissions',

            /*
            |--------------------------------------------------------------------------
            | User Management
            |--------------------------------------------------------------------------
            */

            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            

            'users.activate',
            'users.deactivate',
            'users.import',
            'users.export',

            'users.assign_role',
            'users.assign_permission',

            'users.profile.view',
            'users.profile.edit',
            'users.restore',

            /*
            |--------------------------------------------------------------------------
            | Login History & Security
            |--------------------------------------------------------------------------
            */

            'login_history.view',
            'device_tracking.view',

            /*
            |--------------------------------------------------------------------------
            | Client Management
            |--------------------------------------------------------------------------
            */

            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',

            /*
            |--------------------------------------------------------------------------
            | Project Management
            |--------------------------------------------------------------------------
            */

            'projects.view',
            'projects.create',
            'projects.edit',
            'projects.delete',

            /*
            |--------------------------------------------------------------------------
            | Invoice Management
            |--------------------------------------------------------------------------
            */

            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',

            /*
            |--------------------------------------------------------------------------
            | Activity Logs
            |--------------------------------------------------------------------------
            */

            'activity_logs.view',
            'activity_logs.export',

            /*
            |--------------------------------------------------------------------------
            | Notifications
            |--------------------------------------------------------------------------
            */

            'notifications.view',
            'notifications.create',
            'notifications.delete',

            'login-history.view',

            /*
            |--------------------------------------------------------------------------
            | AI Action Engine
            |--------------------------------------------------------------------------
            */

            'ai.commands.view',
            'ai.commands.execute',
        ];

        foreach ($permissions as $permission) {

            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}