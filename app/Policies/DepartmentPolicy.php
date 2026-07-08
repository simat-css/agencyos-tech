<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    /**
     * View Department List
     */
    public function viewAny(User $user): bool
    {
        return $user->can('departments.view');
    }

    /**
     * View Department
     */
    public function view(User $user, Department $department): bool
    {
        // Super Admin
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Permission + Same Company
        return $user->can('departments.view')
            && $user->company_id === $department->company_id;
    }

    /**
     * Create Department
     */
    public function create(User $user): bool
    {
        return $user->can('departments.create');
    }

    /**
     * Update Department
     */
    public function update(User $user, Department $department): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->can('departments.edit')
            && $user->company_id === $department->company_id;
    }

    /**
     * Delete Department
     */
    public function delete(User $user, Department $department): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->can('departments.delete')
            && $user->company_id === $department->company_id;
    }

    /**
     * Restore
     */
    public function restore(User $user, Department $department): bool
    {
        return $this->delete($user, $department);
    }

    /**
     * Force Delete
     */
    public function forceDelete(User $user, Department $department): bool
    {
        return $user->hasRole('Super Admin');
    }
}