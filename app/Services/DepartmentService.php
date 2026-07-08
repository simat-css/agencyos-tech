<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    /**
     * Get all departments
     */
   public function getAll($search = null)
{
    // $query = Company::with('creator');
    $query = Department::with('company');

    if ($search) {
        $query->where('name', 'like', "%{$search}%");
    }

    return $query->latest()->paginate(10);
}

    /**
     * Create Department
     */
    public function create(array $data): Department
    {
        return DB::transaction(function () use ($data) {

            $data['created_by'] = Auth::id();

            return Department::create($data);
        });
    }

    /**
     * Update Department
     */
    public function update(
        Department $department,
        array $data
    ): Department {

        return DB::transaction(function () use (
            $department,
            $data
        ) {

            $data['updated_by'] = Auth::id();

            $department->update($data);

            return $department->fresh();
        });
    }

    /**
     * Delete Department
     */
    public function delete(
        Department $department
    ): bool {

        return DB::transaction(function () use ($department) {

            return $department->delete();

        });
    }

    /**
     * Toggle Status
     */
    public function toggleStatus(
        Department $department
    ): Department {

        return DB::transaction(function () use ($department) {

            $department->update([

                'status' => ! $department->status,

                'updated_by' => Auth::id(),

            ]);

            return $department->fresh();

        });
    }
}