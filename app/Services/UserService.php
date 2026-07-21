<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\UserActionNotification;
use App\Helpers\ActivityHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\Company;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use App\Notifications\SecurityEventNotification;

class UserService
{
    /*
    |--------------------------------------------------------------------------
    | Create User
    |--------------------------------------------------------------------------
    */

    public function createUser(array $data)
    {
        $authUser = auth()->user();

        if (!$authUser->can("users.create")) {
            throw new \Exception("You do not have permission to create users.");
        }

        /*
    |--------------------------------------------------------------------------
    | Company Validation
    |--------------------------------------------------------------------------
    */

        $company = Company::find($data["company_id"]);

        if (!$company) {
            throw new \Exception("Company not found.");
        }

        if (!$company->status) {
            throw new \Exception("Selected company is inactive.");
        }

        $data["employee_id"] = User::generateEmployeeId($data["company_id"]);

        /*
    |--------------------------------------------------------------------------
    | Company Admin Restriction
    |--------------------------------------------------------------------------
    */

        if (
            $authUser->hasRole("Company Admin") &&
            $data["company_id"] != $authUser->company_id
        ) {
            throw new \Exception(
                "You cannot create users for another company."
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Role Validation
    |--------------------------------------------------------------------------
    */

        $role = $data["role"] ?? null;

        unset($data["role"]);

        if ($role && !$authUser->can("users.assign_role")) {
            throw new \Exception("You cannot assign roles.");
        }

        if ($role === "Super Admin" && !$authUser->hasRole("Super Admin")) {
            throw new \Exception(
                "Only Super Admin can assign Super Admin role."
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Profile Photo Upload
    |--------------------------------------------------------------------------
    */

        if (isset($data["profile_photo"])) {
            $data["profile_photo"] = $data["profile_photo"]->store(
                "users",
                "public"
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Department Validation
    |--------------------------------------------------------------------------
    */

        if (!empty($data["department_id"])) {
            $department = Department::find($data["department_id"]);

            if (!$department) {
                throw new \Exception("Department not found.");
            }

            if ($department->company_id != $data["company_id"]) {
                throw new \Exception(
                    "Selected department does not belong to selected company."
                );
            }

            if (!$department->status) {
                throw new \Exception("Selected department is inactive.");
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Password Validation
    |--------------------------------------------------------------------------
    */

        if (empty($data["password"])) {
            throw new \Exception("Password is required.");
        }

        $data["password"] = Hash::make($data["password"]);

        /*
    |--------------------------------------------------------------------------
    | Create User Transaction
    |--------------------------------------------------------------------------
    */

        $user = DB::transaction(function () use ($data, $role, $authUser) {
            $user = User::create($data);

            if ($role) {
                $user->assignRole($role);
            }

            $user->load(["company", "department", "roles"]);

            $newData = [
                "employee_id" => $user->employee_id,

                "name" => $user->name,

                "email" => $user->email,

                "designation" => $user->designation,

                "phone" => $user->phone,

                "gender" => $user->gender,

                "dob" => $user->dob,

                "joining_date" => $user->joining_date,

                "emergency_contact" => $user->emergency_contact,

                "address" => $user->address,

                "company" => $user->company?->name,

                "department" => $user->department?->name,

                "role" => $user->roles->pluck("name")->implode(", "),

                "status" => $user->status ? "Active" : "Inactive",
            ];

            ActivityHelper::log(
                $authUser,

                $user,

                "User",

                "created",

                [],

                $newData
            );

            return $user;
        });

        /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    */

        $user->notify(
            new UserActionNotification(
                "Your account has been created successfully."
            )
        );

        /*
|--------------------------------------------------------------------------
| Creator Notification
|--------------------------------------------------------------------------
*/

        $authUser->notify(
            new UserActionNotification(
                "User {$user->name} has been created successfully."
            )
        );

        /*
|--------------------------------------------------------------------------
| Super Admin Notification
|--------------------------------------------------------------------------
*/

        if (!$authUser->hasRole("Super Admin")) {
            $superAdmins = User::role("Super Admin")->get();

            foreach ($superAdmins as $admin) {
                $admin->notify(
                    new UserActionNotification(
                        "New user {$user->name} has been created by {$authUser->name}."
                    )
                );
            }
        }

        return $user;
    }

    /*
    |--------------------------------------------------------------------------
    | Update User
    |--------------------------------------------------------------------------
    */

    public function updateUser(User $user, array $data)
    {
        $authUser = auth()->user();

        if (!$authUser->can("users.edit")) {
            throw new \Exception("You do not have permission to edit users.");
        }

        /*
    |--------------------------------------------------------------------------
    | Company Restriction
    |--------------------------------------------------------------------------
    */

        if (
            $authUser->hasRole("Company Admin") &&
            $user->company_id != $authUser->company_id
        ) {
            throw new \Exception("You cannot edit another company user.");
        }

        $companyId = $data["company_id"] ?? $user->company_id;

        if (
            $authUser->hasRole("Company Admin") &&
            $companyId != $authUser->company_id
        ) {
            throw new \Exception("You cannot assign users to another company.");
        }

        /*
    |--------------------------------------------------------------------------
    | Company Validation
    |--------------------------------------------------------------------------
    */

        $company = Company::find($companyId);

        if (!$company) {
            throw new \Exception("Company not found.");
        }

        if (!$company->status) {
            throw new \Exception("Selected company is inactive.");
        }

        /*
    |--------------------------------------------------------------------------
    | Super Admin Protection
    |--------------------------------------------------------------------------
    */

        if (
            $user->hasRole("Super Admin") &&
            !$authUser->hasRole("Super Admin")
        ) {
            throw new \Exception("Super Admin accounts cannot be modified.");
        }

        $role = $data["role"] ?? null;

        unset($data["role"]);

        if ($user->id === $authUser->id && $role && !$user->hasRole($role)) {
            throw new \Exception("You cannot change your own role.");
        }

        if ($role && !$authUser->can("users.assign_role")) {
            throw new \Exception("You cannot assign roles.");
        }

        if ($role === "Super Admin" && !$authUser->hasRole("Super Admin")) {
            throw new \Exception(
                "Only Super Admin can assign Super Admin role."
            );
        }

        if ($role && !Role::where("name", $role)->exists()) {
            throw new \Exception("Selected role not found.");
        }

        /*
    |--------------------------------------------------------------------------
    | Profile Photo Upload
    |--------------------------------------------------------------------------
    */

        if (isset($data["profile_photo"])) {
            if ($user->profile_photo) {
                Storage::disk("public")->delete($user->profile_photo);
            }

            $data["profile_photo"] = $data["profile_photo"]->store(
                "users",
                "public"
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Department Validation
    |--------------------------------------------------------------------------
    */

        if (!empty($data["department_id"])) {
            $department = Department::find($data["department_id"]);

            if (!$department) {
                throw new \Exception("Department not found.");
            }

            if ($department->company_id != $companyId) {
                throw new \Exception(
                    "Selected department does not belong to selected company."
                );
            }

            if (!$department->status) {
                throw new \Exception("Selected department is inactive.");
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Password
    |--------------------------------------------------------------------------
    */

        if (empty($data["password"])) {
            unset($data["password"]);
        } else {
            $data["password"] = Hash::make($data["password"]);
        }

        $roleChanged = false;
        $oldRole = null;
        $newRole = null;
        $changes = [];

        DB::transaction(function () use (
            $user,
            $data,
            $role,
            $authUser,
            &$roleChanged,
            &$oldRole,
            &$newRole,
            &$changes
        ) {
            $user->load(["company", "department", "roles"]);

            $oldData = [
                "employee_id" => $user->employee_id,

                "name" => $user->name,

                "email" => $user->email,

                "designation" => $user->designation,

                "phone" => $user->phone,

                "gender" => $user->gender,

                "dob" => $user->dob,

                "joining_date" => $user->joining_date,

                "emergency_contact" => $user->emergency_contact,

                "address" => $user->address,

                "company" => $user->company?->name,

                "department" => $user->department?->name,

                "role" => $user->roles->pluck("name")->implode(", "),

                "status" => $user->status ? "Active" : "Inactive",
            ];

            $currentRole = $user->roles->pluck("name")->first();

            $user->update($data);

            if ($role && $currentRole !== $role) {
                $roleChanged = true;
                $oldRole = $currentRole;
                $newRole = $role;

                $user->syncRoles([$role]);
            }

            $freshUser = $user
                ->fresh()
                ->load(["company", "department", "roles"]);

            $newData = [
                "employee_id" => $freshUser->employee_id,

                "name" => $freshUser->name,

                "email" => $freshUser->email,

                "designation" => $freshUser->designation,

                "phone" => $freshUser->phone,

                "gender" => $freshUser->gender,

                "dob" => $freshUser->dob,

                "joining_date" => $freshUser->joining_date,

                "emergency_contact" => $freshUser->emergency_contact,

                "address" => $freshUser->address,

                "company" => $freshUser->company?->name,

                "department" => $freshUser->department?->name,

                "role" => $freshUser->roles->pluck("name")->implode(", "),

                "status" => $freshUser->status ? "Active" : "Inactive",
            ];

            $oldValues = [];
            $newValues = [];

            foreach ($newData as $field => $value) {
                if (($oldData[$field] ?? null) != $value) {
                    $oldValues[$field] = $oldData[$field] ?? null;
                    $newValues[$field] = $value;

                    $changes[] =
                        ucfirst($field) .
                        " changed from '" .
                        ($oldData[$field] ?? "N/A") .
                        "' to '" .
                        ($value ?? "N/A") .
                        "'";
                }
            }

            if (!empty($oldValues)) {
                ActivityHelper::log(
                    $authUser,
                    $user,
                    "User",
                    "updated",
                    $oldValues,
                    $newValues
                );
            }
        });

        /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

        if ($roleChanged) {
            $user->notify(
                new SecurityEventNotification(
                    "Security Alert: Your role has been changed from {$oldRole} to {$newRole} by an administrator."
                )
            );
        }

        if (!empty($changes)) {
            $message = "Your account details have been updated.\n\n";
            $message .= implode("\n", $changes);

            $user->notify(new UserActionNotification($message));
        }

        if (!empty($changes)) {
            $authUser->notify(
                new UserActionNotification(
                    "User {$user->name} has been updated successfully."
                )
            );

            if (!$authUser->hasRole("Super Admin")) {
                $superAdmins = User::role("Super Admin")->get();

                foreach ($superAdmins as $admin) {
                    $admin->notify(
                        new UserActionNotification(
                            "User {$user->name} has been updated by {$authUser->name}."
                        )
                    );
                }
            }
        }

        return $user->fresh();
    }
    /*
    |--------------------------------------------------------------------------
    | Delete User
    |--------------------------------------------------------------------------
    */

    public function deleteUser(User $user)
    {
        $authUser = auth()->user();

        if (!$authUser->can("users.delete")) {
            throw new \Exception("You do not have permission to delete users.");
        }

        /*
    |--------------------------------------------------------------------------
    | Self Protection
    |--------------------------------------------------------------------------
    */

        if ($user->id === $authUser->id) {
            throw new \Exception("You cannot delete your own account.");
        }

        /*
    |--------------------------------------------------------------------------
    | Company Restriction
    |--------------------------------------------------------------------------
    */

        if (
            $authUser->hasRole("Company Admin") &&
            $user->company_id != $authUser->company_id
        ) {
            throw new \Exception("You cannot delete another company user.");
        }

        /*
    |--------------------------------------------------------------------------
    | Super Admin Protection
    |--------------------------------------------------------------------------
    */

        if (
            $user->hasRole("Super Admin") &&
            !$authUser->hasRole("Super Admin")
        ) {
            throw new \Exception("Super Admin accounts cannot be deleted.");
        }

        $deletedUserName = $user->name;

        DB::transaction(function () use ($user, $authUser) {
            if ($user->profile_photo) {
                Storage::disk("public")->delete($user->profile_photo);
            }

            $user->load(["company", "department", "roles"]);

            $oldData = [
                "name" => $user->name,
                "email" => $user->email,
                "company" => $user->company?->name,
                "department" => $user->department?->name,
                "role" => $user->roles->pluck("name")->implode(", "),
                "status" => $user->status ? "Active" : "Inactive",
            ];

            ActivityHelper::log(
                $authUser,
                $user,
                "User",
                "deleted",
                $oldData,
                []
            );

            $user->delete();
        });

        $authUser->notify(
            new UserActionNotification(
                "User {$deletedUserName} has been deleted."
            )
        );

        if (!$authUser->hasRole("Super Admin")) {
            $superAdmins = User::role("Super Admin")->get();

            foreach ($superAdmins as $admin) {
                $admin->notify(
                    new UserActionNotification(
                        "User {$deletedUserName} has been deleted by {$authUser->name}."
                    )
                );
            }
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Toggle User Status
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(User $user)
    {
        $authUser = auth()->user();

        if (!$authUser->can("users.edit")) {
            throw new \Exception(
                "You do not have permission to update user status."
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Self Protection
    |--------------------------------------------------------------------------
    */

        if ($user->id === $authUser->id) {
            throw new \Exception("You cannot change your own status.");
        }

        /*
    |--------------------------------------------------------------------------
    | Company Restriction
    |--------------------------------------------------------------------------
    */

        if (
            $authUser->hasRole("Company Admin") &&
            $user->company_id != $authUser->company_id
        ) {
            throw new \Exception("You cannot update another company user.");
        }

        /*
    |--------------------------------------------------------------------------
    | Super Admin Protection
    |--------------------------------------------------------------------------
    */

        if (
            $user->hasRole("Super Admin") &&
            !$authUser->hasRole("Super Admin")
        ) {
            throw new \Exception("Super Admin status cannot be changed.");
        }

        /*
    |--------------------------------------------------------------------------
    | Activation Validation
    |--------------------------------------------------------------------------
    */

        $newStatus = !$user->status;

        if ($newStatus) {
            $check = $this->canActivateUser($user);

            if (!$check["allowed"]) {
                throw new \Exception($check["message"]);
            }
        }

        DB::transaction(function () use ($user, $authUser, $newStatus) {
            $oldData = [
                "status" => $user->status ? "Active" : "Inactive",
            ];

            $user->status = $newStatus;

            $user->save();

            $freshUser = $user
                ->fresh()
                ->load(["company", "department", "roles"]);

            $newData = [
                "status" => $freshUser->status ? "Active" : "Inactive",
            ];

            ActivityHelper::log(
                $authUser,
                $user,
                "User",
                "status_updated",
                $oldData,
                $newData
            );
        });

        $user->refresh();

        $user->notify(
            new UserActionNotification(
                $user->status
                    ? "Your account has been activated."
                    : "Your account has been deactivated."
            )
        );

        $authUser->notify(
            new UserActionNotification(
                "User {$user->name} status has been updated successfully."
            )
        );

        if (!$authUser->hasRole("Super Admin")) {
            $superAdmins = User::role("Super Admin")->get();

            foreach ($superAdmins as $admin) {
                $admin->notify(
                    new UserActionNotification(
                        "User {$user->name} status was changed by {$authUser->name}."
                    )
                );
            }
        }

        return $user;
    }
    /*
|--------------------------------------------------------------------------
| Bulk User Action
|--------------------------------------------------------------------------
*/

    public function bulkAction(array $data)
    {
        $authUser = auth()->user();

        if (!$authUser->can("users.edit")) {
            throw new \Exception("You do not have permission for bulk action.");
        }

        if (empty($data["ids"])) {
            throw new \Exception("Please select at least one user.");
        }

        if (!in_array($data["action"], ["activate", "deactivate", "delete"])) {
            throw new \Exception("Invalid bulk action.");
        }

        $users = User::with(["company", "department", "roles"])
            ->whereIn("id", $data["ids"])
            ->get();

        if ($users->isEmpty()) {
            throw new \Exception("No valid users selected.");
        }

        $processedCount = 0;
        $skippedUsers = [];

        DB::transaction(function () use (
            $users,
            $data,
            $authUser,
            &$processedCount,
            &$skippedUsers
        ) {
            foreach ($users as $user) {
                // Self Protection
                if ($user->id == $authUser->id) {
                    $skippedUsers[] = [
                        "name" => $user->name,
                        "reason" => "You cannot modify your own account.",
                    ];
                    continue;
                }

                // Company Restriction
                if (
                    $authUser->hasRole("Company Admin") &&
                    $user->company_id != $authUser->company_id
                ) {
                    $skippedUsers[] = [
                        "name" => $user->name,
                        "reason" => "User belongs to another company.",
                    ];
                    continue;
                }

                // Super Admin Protection
                if (
                    $user->hasRole("Super Admin") &&
                    !$authUser->hasRole("Super Admin")
                ) {
                    $skippedUsers[] = [
                        "name" => $user->name,
                        "reason" => "Super Admin accounts cannot be modified.",
                    ];
                    continue;
                }

                switch ($data["action"]) {
                    case "activate":
                        if ($user->status == 1) {
                            $skippedUsers[] = [
                                "name" => $user->name,
                                "reason" => "User is already active.",
                            ];
                            continue 2;
                        }

                        $check = $this->canActivateUser($user);

                        if (!$check["allowed"]) {
                            $skippedUsers[] = [
                                "name" => $user->name,
                                "reason" => $check["message"],
                            ];
                            continue 2;
                        }

                        $oldData = [
                            "status" => "Inactive",
                        ];

                        $user->status = 1;

                        $message = "Your account has been activated.";

                        break;

                    case "deactivate":
                        if ($user->status == 0) {
                            $skippedUsers[] = [
                                "name" => $user->name,
                                "reason" => "User is already inactive.",
                            ];
                            continue 2;
                        }

                        $oldData = [
                            "status" => "Active",
                        ];

                        $user->status = 0;

                        $message = "Your account has been deactivated.";

                        break;

                    case "delete":
                        if (!$authUser->can("users.delete")) {
                            throw new \Exception(
                                "You do not have permission to delete users."
                            );
                        }

                        $user->load(["company", "department", "roles"]);

                        $oldData = [
                            "name" => $user->name,
                            "email" => $user->email,
                            "company" => $user->company?->name,
                            "department" => $user->department?->name,
                            "role" => $user->roles
                                ->pluck("name")
                                ->implode(", "),
                            "status" => $user->status ? "Active" : "Inactive",
                        ];

                        if ($user->profile_photo) {
                            Storage::disk("public")->delete(
                                $user->profile_photo
                            );
                        }

                        ActivityHelper::log(
                            $authUser,
                            $user,
                            "User",
                            "bulk_deleted",
                            $oldData,
                            []
                        );

                        $authUser->notify(
                            new UserActionNotification(
                                "User {$user->name} has been deleted using bulk action."
                            )
                        );

                        if (!$authUser->hasRole("Super Admin")) {
                            $superAdmins = User::role("Super Admin")->get();

                            foreach ($superAdmins as $admin) {
                                $admin->notify(
                                    new UserActionNotification(
                                        "User {$user->name} was deleted by {$authUser->name} using bulk action."
                                    )
                                );
                            }
                        }

                        $user->delete();

                        $processedCount++;

                        continue 2;
                }

                $user->save();

                $freshUser = $user->fresh();

                $newData = [
                    "status" => $freshUser->status ? "Active" : "Inactive",
                ];

                ActivityHelper::log(
                    $authUser,
                    $user,
                    "User",
                    "bulk_" . $data["action"],
                    $oldData,
                    $newData
                );

                $user->notify(new UserActionNotification($message));
                $authUser->notify(
                    new UserActionNotification(
                        "Bulk {$data["action"]} completed for user {$user->name}."
                    )
                );

                if (!$authUser->hasRole("Super Admin")) {
                    $superAdmins = User::role("Super Admin")->get();

                    foreach ($superAdmins as $admin) {
                        $admin->notify(
                            new UserActionNotification(
                                "User {$user->name} was {$data["action"]}d by {$authUser->name} using bulk action."
                            )
                        );
                    }
                }

                $processedCount++;
            }
        });

        if ($processedCount === 0) {
            $reasons = collect($skippedUsers)
                ->pluck("reason")
                ->unique()
                ->implode(" | ");

            return [
                "success" => false,
                "processed" => 0,
                "skipped" => $skippedUsers,
                "message" => $reasons ?: "No users could be processed.",
            ];
        }

        return [
            "success" => true,
            "processed" => $processedCount,
            "skipped" => $skippedUsers,
            "message" => "{$processedCount} user(s) processed successfully.",
        ];
    }

    private function canActivateUser(User $user): array
    {
        if (!$user->company) {
            return [
                "allowed" => false,
                "message" => "User company is not assigned.",
            ];
        }

        if ($user->company->status == 0) {
            return [
                "allowed" => false,
                "message" =>
                    "Cannot activate user because company is inactive.",
            ];
        }

        if ($user->department_id) {
            if (!$user->department) {
                return [
                    "allowed" => false,
                    "message" => "Assigned department not found.",
                ];
            }

            if ($user->department->status == 0) {
                return [
                    "allowed" => false,
                    "message" =>
                        "Cannot activate user because department is inactive.",
                ];
            }
        }

        return [
            "allowed" => true,
            "message" => "User can be activated.",
        ];
    }

    //AI Part
    public function emailExists(string $email): bool
    {
        return User::where("email", $email)->exists();
    }
    public function findUserByEmail(string $email)
    {
        return User::where("email", $email)->first();
    }
    public function emailExistsForOtherUser(string $email, int $userId): bool
    {
        return User::where("email", $email)
            ->where("id", "!=", $userId)
            ->exists();
    }
    public function findCompany(string $name)
    {
        return Company::where("name", "like", "%{$name}%")->first();
    }
    public function findDepartment(int $companyId, string $name)
    {
        return Department::where("company_id", $companyId)
            ->where("name", "like", "%{$name}%")
            ->first();
    }
    public function findRole(string $role)
    {
        return Role::where("name", $role)->first();
    }
    public function findUserById(int $id)
    {
        return User::find($id);
    }
    public function getUsersByStatus(int $status, ?int $companyId = null)
    {
        return User::with(["company", "department", "roles"])
            ->when(
                $companyId,
                fn($query) => $query->where("company_id", $companyId)
            )
            ->where("status", $status)
            ->orderBy("name")
            ->get();
    }
    public function findDeletedUserByEmail(string $email)
    {
        return User::onlyTrashed()
            ->where("email", $email)
            ->first();
    }
    public function restoreUser(User $user): bool
    {
        $restored = $user->restore();

        if ($restored) {
            ActivityHelper::log(
                auth()->user(),
                $user,
                "User",
                "restored",
                [],
                [
                    "name" => $user->name,
                    "email" => $user->email,
                    "company_id" => $user->company_id,
                    "status" => $user->status ? "Active" : "Inactive",
                ]
            );

            $user->notify(
                new UserActionNotification("Your account has been restored.")
            );

            auth()
                ->user()
                ->notify(
                    new UserActionNotification(
                        "User {$user->name} has been restored successfully."
                    )
                );

            if (
                !auth()
                    ->user()
                    ->hasRole("Super Admin")
            ) {
                $superAdmins = User::role("Super Admin")->get();

                foreach ($superAdmins as $admin) {
                    $admin->notify(
                        new UserActionNotification(
                            "User {$user->name} has been restored by " .
                                auth()->user()->name .
                                "."
                        )
                    );
                }
            }
        }

        return $restored;
    }

    public function findDeletedUsers(?int $companyId = null)
    {
        return User::onlyTrashed()
            ->when(
                $companyId,
                fn($query) => $query->where("company_id", $companyId)
            )
            ->with(["company", "department", "roles"])
            ->orderBy("name")
            ->get();
    }
    public function findDeletedUserById(int $id)
    {
        return User::withTrashed()->find($id);
    }
}
