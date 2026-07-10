<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class UserPermissionSeeder extends Seeder
{
    public function run(): void
    {

        $permissions = [

            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

        ];


        foreach ($permissions as $permission) {

            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);

        }

    }
}