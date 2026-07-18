<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityHelper;
use App\Notifications\UserActionNotification;

class DepartmentService
{
    /**
     * Get all departments
     */
    public function getAll($search = null)
    {
        $query = Department::with("company");

        if ($search) {
            $query->where("name", "like", "%{$search}%");
        }

        return $query->latest()->paginate(10);
    }

    /**
     * Create Department
     */
    /**
     * Create Department
     */
    public function create(array $data): Department
    {
        $authUser = auth()->user();

        /*
    |--------------------------------------------------------------------------
    | Permission Check
    |--------------------------------------------------------------------------
    */

        if (!$authUser->can("departments.create")) {
            throw new \Exception(
                "You do not have permission to create departments."
            );
        }

        return DB::transaction(function () use ($data, $authUser) {
            /*
        |--------------------------------------------------------------------------
        | Create Department
        |--------------------------------------------------------------------------
        */

            $data["created_by"] = Auth::id();

            $department = Department::create($data);

            /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
            $department->load("company");

            ActivityHelper::log(
                $authUser,
                $department,
                "department",
                "created",
                [],
                [
                    "name" => $department->name,
                    "code" => $department->code,
                    "company" => $department->company?->name,
                    "status" => $department->status ? "Active" : "Inactive",
                ]
            );

            /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

            $authUser->notify(
                new UserActionNotification(
                    "Department {$department->name} has been created."
                )
            );

            return $department;
        });
    }

    /**
     * Update Department
     */
    public function update(Department $department, array $data): Department
    {
        $authUser = auth()->user();

        if (!$authUser->can("departments.edit")) {
            throw new \Exception(
                "You do not have permission to edit departments."
            );
        }

        return DB::transaction(function () use ($department, $data, $authUser) {
            /*
        |--------------------------------------------------------------------------
        | Old Data For Activity Log
        |--------------------------------------------------------------------------
        */

            $department->load("company");

            $oldData = [
                "name" => $department->name,
                "code" => $department->code,
                "company" => $department->company?->name,
                "status" => $department->status ? "Active" : "Inactive",
            ];

            /*
        |--------------------------------------------------------------------------
        | Update Department
        |--------------------------------------------------------------------------
        */

            $data["updated_by"] = Auth::id();

            $department->update($data);

            /*
        |--------------------------------------------------------------------------
        | Refresh Department Once
        |--------------------------------------------------------------------------
        */

            $department->refresh()->load("company");

            /*
        |--------------------------------------------------------------------------
        | New Data For Activity Log
        |--------------------------------------------------------------------------
        */

            $newData = [
                "name" => $department->name,
                "code" => $department->code,
                "company" => $department->company?->name,
                "status" => $department->status ? "Active" : "Inactive",
            ];

            $oldValues = [];
            $newValues = [];

            foreach ($newData as $field => $value) {
                if (($oldData[$field] ?? null) != $value) {
                    $oldValues[$field] = $oldData[$field];

                    $newValues[$field] = $value;
                }
            }

            /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

            if (!empty($oldValues)) {
                ActivityHelper::log(
                    $authUser,
                    $department,
                    "department",
                    "updated",
                    $oldValues,
                    $newValues
                );
            }

            /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

            $authUser->notify(
                new UserActionNotification(
                    "Department {$department->name} has been updated."
                )
            );

            return $department;
        });
    }

    /**
     * Delete Department
     */
    public function delete(Department $department): bool
    {
        $authUser = auth()->user();

        /*
    |--------------------------------------------------------------------------
    | Permission Check
    |--------------------------------------------------------------------------
    */

        if (!$authUser->can("departments.delete")) {
            throw new \Exception(
                "You do not have permission to delete departments."
            );
        }

        return DB::transaction(function () use ($department, $authUser) {
            /*
        |--------------------------------------------------------------------------
        | Store Old Data Before Delete
        |--------------------------------------------------------------------------
        */

            $oldData = [
                "name" => $department->name,
                "code" => $department->code,
                "company" => $department->company?->name,
                "status" => $department->status ? "Active" : "Inactive",
            ];

            $departmentName = $department->name;

            /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

            ActivityHelper::log(
                $authUser,
                $department,
                "department",
                "deleted",
                $oldData,
                []
            );

            /*
        |--------------------------------------------------------------------------
        | Soft Delete
        |--------------------------------------------------------------------------
        */

            $department->delete();

            /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

            $authUser->notify(
                new UserActionNotification(
                    "Department {$departmentName} has been deleted."
                )
            );

            return true;
        });
    }

    /**
     * Toggle Status
     */
    /**
     * Toggle Status
     */
    public function toggleStatus(Department $department): Department
    {
        $authUser = auth()->user();

        /*
    |--------------------------------------------------------------------------
    | Permission Check
    |--------------------------------------------------------------------------
    */

        if (!$authUser->can("departments.edit")) {
            throw new \Exception(
                "You do not have permission to update department status."
            );
        }

        return DB::transaction(function () use ($department, $authUser) {
            /*
        |--------------------------------------------------------------------------
        | Old Data
        |--------------------------------------------------------------------------
        */

            $oldData = [
                "status" => $department->status ? "Active" : "Inactive",
            ];

            /*
        |--------------------------------------------------------------------------
        | Update Status
        |--------------------------------------------------------------------------
        */

            $newStatus = !$department->status;

            $department->update([
                "status" => $newStatus,

                "updated_by" => Auth::id(),
            ]);

            // If department deactivated
            if (!$newStatus) {
                $this->deactivateDepartmentUsers($department);
            }

            /*
        |--------------------------------------------------------------------------
        | Refresh Model
        |--------------------------------------------------------------------------
        */

            $department->refresh();

            /*
        |--------------------------------------------------------------------------
        | New Data
        |--------------------------------------------------------------------------
        */

            $newData = [
                "status" => $department->status ? "Active" : "Inactive",
            ];

            /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

            ActivityHelper::log(
                $authUser,
                $department,
                "department",
                "status_updated",
                $oldData,
                $newData
            );

            /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

            $message = $department->status
                ? "Department {$department->name} has been activated."
                : "Department {$department->name} has been deactivated.";

            $authUser->notify(new UserActionNotification($message));

            return $department;
        });
    }

    /**
     * Deactivate Department Users
     */
    private function deactivateDepartmentUsers(Department $department): void
    {
        $department
            ->users()
            ->where("status", 1)
            ->update([
                "status" => 0,
            ]);
    }
    /**
     * Bulk Deactivate Departments
     */
    public function bulkDeactivate(array $departmentIds): array
    {
        $authUser = auth()->user();

        if (!$authUser->can("departments.edit")) {
            throw new \Exception(
                "You do not have permission to deactivate departments."
            );
        }

        return DB::transaction(function () use ($departmentIds, $authUser) {
            $departments = Department::whereIn("id", $departmentIds)->get();

            $deactivatedCount = 0;

            $alreadyInactive = [];

            foreach ($departments as $department) {
                if (!$department->status) {
                    $alreadyInactive[] = $department->name;

                    continue;
                }

                /*
            |--------------------------------------------------------------------------
            | Old Data
            |--------------------------------------------------------------------------
            */

                $oldData = [
                    "status" => "Active",
                ];

                /*
            |--------------------------------------------------------------------------
            | Deactivate Department
            |--------------------------------------------------------------------------
            */

                $department->update([
                    "status" => 0,

                    "updated_by" => Auth::id(),
                ]);

                /*
            |--------------------------------------------------------------------------
            | Deactivate Related Users
            |--------------------------------------------------------------------------
            */

                $department
                    ->users()
                    ->where("status", 1)
                    ->update([
                        "status" => 0,
                    ]);

                /*
            |--------------------------------------------------------------------------
            | Activity Log
            |--------------------------------------------------------------------------
            */

                $newData = [
                    "status" => "Inactive",
                ];
                ActivityHelper::log(
                    $authUser,
                    $department,
                    "department",
                    "bulk_deactivated",
                    $oldData,
                    $newData
                );

                $deactivatedCount++;
            }

            return [
                "deactivated" => $deactivatedCount,

                "alreadyInactive" => $alreadyInactive,
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | AI Department Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Find Department by ID
     */
    public function findById(int $id): ?Department
    {
        return Department::with(["company", "users"])->find($id);
    }

    /**
     * Find Department by Name
     */
    public function findByName(
        string $name,
        ?int $companyId = null
    ): ?Department {
        $query = Department::with("company")->where(
            "name",
            "like",
            "%{$name}%"
        );

        if ($companyId) {
            $query->where("company_id", $companyId);
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
        return Department::where("code", $code)
            ->where("company_id", $companyId)
            ->when($ignoreId, fn($q) => $q->where("id", "!=", $ignoreId))
            ->exists();
    }

    /**
     * Get Active Departments
     */
    public function getActiveDepartments(?int $companyId = null)
    {
        return Department::with("company")
            ->where("status", 1)
            ->when($companyId, fn($q) => $q->where("company_id", $companyId))
            ->orderBy("name")
            ->get();
    }

    /**
     * Get Inactive Departments
     */
    public function getInactiveDepartments(?int $companyId = null)
    {
        return Department::with("company")
            ->where("status", 0)
            ->when($companyId, fn($q) => $q->where("company_id", $companyId))
            ->orderBy("name")
            ->get();
    }

    /**
     * Search Departments
     */
    public function search(string $keyword, ?int $companyId = null)
    {
        return Department::with(["company", "users"])
            ->where(function ($q) use ($keyword) {
                $q->where("name", "like", "%{$keyword}%")->orWhere(
                    "code",
                    "like",
                    "%{$keyword}%"
                );
            })
            ->when($companyId, fn($q) => $q->where("company_id", $companyId))
            ->orderBy("name")
            ->get();
    }

    /**
     * Department Statistics
     */
    public function statistics(?int $companyId = null): array
    {
        $query = Department::query();

        if ($companyId) {
            $query->where("company_id", $companyId);
        }

        $total = (clone $query)->count();

        $active = (clone $query)->where("status", 1)->count();

        $inactive = (clone $query)->where("status", 0)->count();

        return [
            "total" => $total,
            "active" => $active,
            "inactive" => $inactive,
        ];
    }

    /**
     * Activate Department
     */
    //Activate Department
    public function activate(Department $department): Department
    {
        $authUser = auth()->user();

        /*
    |--------------------------------------------------------------------------
    | Permission Check
    |--------------------------------------------------------------------------
    */

        if (!$authUser->can("departments.edit")) {
            throw new \Exception(
                "You do not have permission to activate departments."
            );
        }

        return DB::transaction(function () use ($department, $authUser) {
            /*
        |--------------------------------------------------------------------------
        | Old Data
        |--------------------------------------------------------------------------
        */

            $oldData = [
                "status" => "Inactive",
            ];

            /*
        |--------------------------------------------------------------------------
        | Activate Department
        |--------------------------------------------------------------------------
        */

            $department->update([
                "status" => 1,

                "updated_by" => auth()->id(),
            ]);

            /*
        |--------------------------------------------------------------------------
        | New Data
        |--------------------------------------------------------------------------
        */

            $newData = [
                "status" => "Active",
            ];

            /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

            ActivityHelper::log(
                $authUser,
                $department,
                "department",
                "activated",
                $oldData,
                $newData
            );

            /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

            $authUser->notify(
                new UserActionNotification(
                    "Department {$department->name} has been activated."
                )
            );

            return $department->fresh();
        });
    }

    //Deactivate Department
    public function deactivate(Department $department): Department
    {
        $authUser = auth()->user();

        /*
    |--------------------------------------------------------------------------
    | Permission Check
    |--------------------------------------------------------------------------
    */

        if (!$authUser->can("departments.edit")) {
            throw new \Exception(
                "You do not have permission to deactivate departments."
            );
        }

        return DB::transaction(function () use ($department, $authUser) {
            /*
        |--------------------------------------------------------------------------
        | Old Data
        |--------------------------------------------------------------------------
        */

            $oldData = [
                "status" => "Active",
            ];
            /*
        |--------------------------------------------------------------------------
        | Deactivate Department
        |--------------------------------------------------------------------------
        */

            $department->update([
                "status" => 0,

                "updated_by" => auth()->id(),
            ]);

            /*
        |--------------------------------------------------------------------------
        | New Data
        |--------------------------------------------------------------------------
        */

            $newData = [
                "status" => "Inactive",
            ];

            /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

            ActivityHelper::log(
                $authUser,
                $department,
                "department",
                "deactivated",
                $oldData,
                $newData
            );

            /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

            $authUser->notify(
                new UserActionNotification(
                    "Department {$department->name} has been deactivated."
                )
            );

            return $department->fresh();
        });
    }

    /**
     * Find Department by Name and Company
     */
    public function findByNameAndCompany(string $departmentName, int $companyId)
    {
        return Department::where("company_id", $companyId)
            ->whereRaw("LOWER(TRIM(name)) = ?", [
                strtolower(trim($departmentName)),
            ])
            ->first();
    }

    /**
     * Find Deleted Department
     */
    public function findDeletedDepartment(
        string $name,
        int $companyId
    ): ?Department {
        return Department::onlyTrashed()
            ->where("company_id", $companyId)
            ->whereRaw("LOWER(TRIM(name)) = ?", [strtolower(trim($name))])
            ->first();
    }
}
