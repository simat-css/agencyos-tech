<?php

namespace App\Services;

use App\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleService
{
    /*
    |--------------------------------------------------------------------------
    | System Roles
    |--------------------------------------------------------------------------
    */

public function getSystemRoles()
{
    return Role::with('permissions')
        ->where('is_system', true)
        ->orderBy('name')
        ->paginate(10, ['*'], 'system_page')
        ->withQueryString();
}

public function getCustomRoles()
{
    return Role::with(['permissions','company'])

        ->where('is_system', false)

        ->when(
            !auth()->user()->hasRole('Super Admin'),
            function ($query) {

                $query->where(
                    'company_id',
                    auth()->user()->company_id
                );

            }
        )

        ->when(
            request('search'),
            function ($query) {

                $query->where(
                    'name',
                    'like',
                    '%' . request('search') . '%'
                );

            }
        )

        ->latest()
        ->paginate(10)
        ->withQueryString();
}

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */

    public function getPermissions()
    {
        return Permission::orderBy('name')->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Create Role
    |--------------------------------------------------------------------------
    */

public function create(array $data)
{
    $user = auth()->user();

    $isSuperAdmin = $user->hasRole('Super Admin');


    $role = Role::create([

        'name'       => $data['name'],

        'guard_name' => 'web',

        'company_id' => $isSuperAdmin
            ? null
            : $user->company_id,

        'is_system'  => $isSuperAdmin,

    ]);


    $role->syncPermissions(
        $data['permissions'] ?? []
    );


    return $role;
}

    /*
    |--------------------------------------------------------------------------
    | Update Role
    |--------------------------------------------------------------------------
    */

    public function update(Role $role, array $data)
    {
        if (
            $role->is_system &&
            !auth()->user()->hasRole('Super Admin')
        ) {

            throw new \Exception(
                'System roles can only be managed by Super Admin.'
            );
        }

        if (
            !$role->is_system &&
            !auth()->user()->hasRole('Super Admin') &&
            $role->company_id != auth()->user()->company_id
        ) {

            throw new \Exception(
                'You cannot manage roles from another company.'
            );
        }

        $role->update([

            'name' => $data['name'],

        ]);

        $role->syncPermissions(
            $data['permissions'] ?? []
        );

        return $role;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Role
    |--------------------------------------------------------------------------
    */

    public function delete(Role $role)
    {
        if (
            $role->is_system &&
            !auth()->user()->hasRole('Super Admin')
        ) {

            throw new \Exception(
                'System roles can only be deleted by Super Admin.'
            );
        }

        if (
            !$role->is_system &&
            !auth()->user()->hasRole('Super Admin') &&
            $role->company_id != auth()->user()->company_id
        ) {

            throw new \Exception(
                'You cannot delete roles from another company.'
            );
        }

        return $role->delete();
    }
}