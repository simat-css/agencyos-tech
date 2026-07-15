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

    //AI DEPARTMENT
    /**
 * Find Department by ID
 */
public function findById(int $id): ?Department
{
    return Department::with([
        'company',
        'users'
    ])->find($id);
}

/**
 * Find Department by Name
 */
public function findByName(
    string $name,
    ?int $companyId = null
): ?Department {

    $query = Department::with('company')
        ->where('name', 'like', "%{$name}%");

    if ($companyId) {
        $query->where('company_id', $companyId);
    }

    return $query->first();
}
/**
 * Check Duplicate Department Code
 */
public function codeExists(
    string $code,
    int $companyId,
    ?int $ignoreId = null
): bool {

    return Department::where(
        'code',
        $code
    )
    ->where(
        'company_id',
        $companyId
    )
    ->when(
        $ignoreId,
        fn ($q) =>
            $q->where(
                'id',
                '!=',
                $ignoreId
            )
    )
    ->exists();
}
//Active Departments
public function getActiveDepartments(
    ?int $companyId = null
) {

    return Department::with('company')
        ->where('status', 1)
        ->when(
            $companyId,
            fn ($q) =>
                $q->where(
                    'company_id',
                    $companyId
                )
        )
        ->orderBy('name')
        ->get();
}
//Inactive Departments
public function getInactiveDepartments(
    ?int $companyId = null
) {

    return Department::with('company')
        ->where('status', 0)
        ->when(
            $companyId,
            fn ($q) =>
                $q->where(
                    'company_id',
                    $companyId
                )
        )
        ->orderBy('name')
        ->get();
}
//Search Departments
public function search(
    string $keyword,
    ?int $companyId = null
) {

    return Department::with([
        'company',
        'users'
    ])
    ->where(function ($q) use ($keyword) {

        $q->where(
            'name',
            'like',
            "%{$keyword}%"
        )
        ->orWhere(
            'code',
            'like',
            "%{$keyword}%"
        );

    })
    ->when(
        $companyId,
        fn ($q) =>
            $q->where(
                'company_id',
                $companyId
            )
    )
    ->orderBy('name')
    ->get();
}
//Statistics
public function statistics(
    ?int $companyId = null
): array {

    $query = Department::query();

    if ($companyId) {
        $query->where(
            'company_id',
            $companyId
        );
    }

    $total = (clone $query)->count();

    $active = (clone $query)
        ->where('status', 1)
        ->count();

    $inactive = (clone $query)
        ->where('status', 0)
        ->count();

    return [
        'total' => $total,
        'active' => $active,
        'inactive' => $inactive,
    ];
}
//Activate Department
public function activate(
    Department $department
): Department {

    $department->update([
        'status' => 1,
        'updated_by' => auth()->id(),
    ]);

    return $department->fresh();
}
//Deactivate Department
public function deactivate(
    Department $department
): Department {

    $department->update([
        'status' => 0,
        'updated_by' => auth()->id(),
    ]);

    return $department->fresh();
}
public function findByNameAndCompany(
    string $departmentName,
    int $companyId
) {
    return Department::where('company_id', $companyId)
        ->whereRaw(
            'LOWER(TRIM(name)) = ?',
            [
                strtolower(trim($departmentName))
            ]
        )
        ->first();
}
}