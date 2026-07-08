<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

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

        ];

        foreach ($permissions as $permission) {

            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);

        }
    }
}