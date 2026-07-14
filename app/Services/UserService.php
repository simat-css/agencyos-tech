<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\UserActionNotification;
use App\Helpers\ActivityHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

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

        if (
            $authUser->hasRole("Company Admin") &&
            $data["company_id"] != $authUser->company_id
        ) {
            throw new \Exception(
                "You cannot create users for another company."
            );
        }

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

        if (isset($data["profile_photo"])) {
            $data["profile_photo"] = $data["profile_photo"]->store(
                "users",
                "public"
            );
        }

        if (!empty($data["department_id"])) {
            $department = \App\Models\Department::find($data["department_id"]);

            if ($department && $department->company_id != $data["company_id"]) {
                throw new \Exception(
                    "Selected department does not belong to selected company."
                );
            }
        }

        if (empty($data["password"])) {
            throw new \Exception("Password is required.");
        }

        $data["password"] = Hash::make($data["password"]);

        $user = DB::transaction(function () use ($data, $role, $authUser) {
            $user = User::create($data);

            if ($role) {
                $user->assignRole($role);
            }

            $newData = $user->toArray();

            $newData["roles"] = $user->roles->pluck("name")->toArray();

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

        $user->notify(
            new UserActionNotification(
                "Your account has been created successfully."
            )
        );

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

        if (
            $authUser->hasRole("Company Admin") &&
            $data["company_id"] != $authUser->company_id
        ) {
            throw new \Exception("You cannot assign users to another company.");
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
        if (isset($data["profile_photo"])) {
            if ($user->profile_photo) {
                Storage::disk("public")->delete($user->profile_photo);
            }

            $data["profile_photo"] = $data["profile_photo"]->store(
                "users",
                "public"
            );
        }
        if (!empty($data["department_id"])) {
            $department = \App\Models\Department::find($data["department_id"]);

            if ($department && $department->company_id != $data["company_id"]) {
                throw new \Exception(
                    "Selected department does not belong to selected company."
                );
            }
        }

        if (empty($data["password"])) {
            unset($data["password"]);
        } else {
            $data["password"] = Hash::make($data["password"]);
        }

        DB::transaction(function () use ($user, $data, $role, $authUser) {
            $oldData = $user->toArray();

            $oldData["roles"] = $user->roles->pluck("name")->toArray();

            $currentRole = $user->roles->pluck("name")->first();

            $user->update($data);

            if ($role && $currentRole !== $role) {
                $user->syncRoles([$role]);

                $user->notify(
                    new UserActionNotification(
                        "Your role has been changed to {$role}."
                    )
                );
            }

            $freshUser = $user->fresh();

            $newData = $freshUser->toArray();

            $newData["roles"] = $freshUser->roles->pluck("name")->toArray();

            ActivityHelper::log(
                $authUser,
                $user,
                "User",
                "updated",
                $oldData,
                $newData
            );
        });

        $user->notify(
            new UserActionNotification(
                "Your account details have been updated."
            )
        );

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

            $oldData = $user->toArray();

            $oldData["roles"] = $user->roles->pluck("name")->toArray();

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
    | Super Admin Protection (Recommended)
    |--------------------------------------------------------------------------
    */

        if (
            $user->hasRole("Super Admin") &&
            !$authUser->hasRole("Super Admin")
        ) {
            throw new \Exception("Super Admin status cannot be changed.");
        }

        DB::transaction(function () use ($user, $authUser) {
            $oldData = $user->toArray();

            $oldData["roles"] = $user->roles->pluck("name")->toArray();

            $user->status = !$user->status;

            $user->save();

            $freshUser = $user->fresh();

            $newData = $freshUser->toArray();

            $newData["roles"] = $freshUser->roles->pluck("name")->toArray();

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

        $users = User::whereIn("id", $data["ids"])->get();
        if ($users->isEmpty()) {
            throw new \Exception("No valid users selected.");
        }

        DB::transaction(function () use ($users, $data, $authUser) {
            foreach ($users as $user) {
                /*
            |--------------------------------------------------------------------------
            | Self Protection
            |--------------------------------------------------------------------------
            */

                if ($user->id === $authUser->id) {
                    throw new \Exception(
                        "You cannot perform bulk actions on your own account."
                    );
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
                    throw new \Exception(
                        "You cannot modify another company user."
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | Super Admin Protection (Optional but Recommended)
            |--------------------------------------------------------------------------
            */

                if (
                    $user->hasRole("Super Admin") &&
                    !$authUser->hasRole("Super Admin")
                ) {
                    throw new \Exception(
                        "Super Admin accounts cannot be modified."
                    );
                }

                switch ($data["action"]) {
                    case "activate":
                        $oldData = $user->toArray();

                        $oldData["roles"] = $user->roles
                            ->pluck("name")
                            ->toArray();

                        $user->status = 1;

                        $message = "Your account has been activated.";

                        break;

                    case "deactivate":
                        $oldData = $user->toArray();

                        $oldData["roles"] = $user->roles
                            ->pluck("name")
                            ->toArray();

                        $user->status = 0;

                        $message = "Your account has been deactivated.";

                        break;

                    case "delete":
                        if (!$authUser->can("users.delete")) {
                            throw new \Exception(
                                "You do not have permission to delete users."
                            );
                        }

                        if ($user->profile_photo) {
                            Storage::disk("public")->delete(
                                $user->profile_photo
                            );
                        }

                        $oldData = $user->toArray();

                        $oldData["roles"] = $user->roles
                            ->pluck("name")
                            ->toArray();

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
                                "User {$user->name} deleted through bulk action."
                            )
                        );

                        $user->delete();

                        continue 2;
                }

                $user->save();

                $freshUser = $user->fresh();

                $newData = $freshUser->toArray();

                $newData["roles"] = $freshUser->roles->pluck("name")->toArray();

                ActivityHelper::log(
                    $authUser,
                    $user,
                    "User",
                    "bulk_" . $data["action"],
                    $oldData,
                    $newData
                );

                $user->notify(new UserActionNotification($message));
            }
        });

        return true;
    }
}
