<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Display Roles List
     */
public function index(Request $request)
{
    $search = $request->search;

    $systemRoles = Role::query()
        ->whereNull('company_id');

    $customRoles = Role::query()
        ->whereNotNull('company_id');

    if ($search) {

        $systemRoles->where('name', 'like', "%{$search}%");

        $customRoles->where('name', 'like', "%{$search}%");
    }

    $systemRoles = $systemRoles
        ->with('permissions')
        ->latest()
        ->paginate(10, ['*'], 'system_page');

    $customRoles = $customRoles
        ->with(['permissions', 'company'])
        ->latest()
        ->paginate(10, ['*'], 'custom_page');

    $permissionsCount = Permission::count();

    return view('roles.index', compact(
        'systemRoles',
        'customRoles',
        'permissionsCount'
    ));
}

    /**
     * Show Create Form
     */
    public function create()
    {
        $permissions = $this->roleService->getPermissions();

        return view(
            'roles.create',
            compact('permissions')
        );
    }

    /**
     * Store Role
     */
    public function store(StoreRoleRequest $request)
    {
        try {

            $this->roleService->create(
                $request->validated()
            );

            return redirect()
                ->route('roles.index')
                ->with(
                    'success',
                    'Role created successfully.'
                );

        } catch (\Exception $e) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /**
     * Show Edit Form
     */
    public function edit(Role $role)
    {
        $permissions = $this->roleService->getPermissions();

        return view(
            'roles.edit',
            compact(
                'role',
                'permissions'
            )
        );
    }

    /**
     * Update Role
     */
    public function update(
        UpdateRoleRequest $request,
        Role $role
    ) {
        try {

            $this->roleService->update(
                $role,
                $request->validated()
            );

            return redirect()
                ->route('roles.index')
                ->with(
                    'success',
                    'Role updated successfully.'
                );

        } catch (\Exception $e) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    //show 
    public function show(Role $role)
{
    return view(
        'roles.show',
        compact('role')
    );
} 
    /**
     * Delete Role
     */
    public function destroy(Role $role)
    {
        try {

            $this->roleService->delete($role);

            return redirect()
                ->route('roles.index')
                ->with(
                    'success',
                    'Role deleted successfully.'
                );

        } catch (\Exception $e) {

            return redirect()
                ->route('roles.index')
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }
}