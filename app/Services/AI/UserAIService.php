<?php

namespace App\Services\AI;

use App\Services\UserService;

class UserAIService
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function process(string $command): array
    {
        $command = trim($command);
        // Clear old pending actions before new command
        if (
            strtolower($command) !== "confirm" &&
            !str_contains(strtolower($command), "update")
        ) {
            session()->forget([
                "pending_user_create",
                "pending_user_update",
                "pending_user_delete",
                "pending_user_status",
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Create User
    |--------------------------------------------------------------------------
    */

        if (str_contains(strtolower($command), "create user")) {
            return $this->requestUserCreateConfirmation($command);
        }

        // Update User
        if (str_contains(strtolower($command), "update user")) {
            return $this->requestUserUpdateConfirmation($command);
        }

        /*
|--------------------------------------------------------------------------
| Delete User
|--------------------------------------------------------------------------
*/

        if (str_contains(strtolower($command), "delete user")) {
            return $this->requestUserDeleteConfirmation($command);
        }
        //Restore users
        if (str_contains(strtolower($command), "restore user")) {
            return $this->requestUserRestoreConfirmation($command);
        }
        /*
|--------------------------------------------------------------------------
| Activate / Deactivate User
|--------------------------------------------------------------------------
*/

        if (
            str_contains(strtolower($command), "activate user") ||
            str_contains(strtolower($command), "deactivate user")
        ) {
            return $this->requestUserStatusConfirmation($command);
        }

        if (str_contains(strtolower($command), "show user")) {
            return $this->showUser($command);
        }
        if (strtolower($command) === "show active users") {
            return $this->showUsersByStatus(1);
        }

        if (strtolower($command) === "show inactive users") {
            return $this->showUsersByStatus(0);
        }

        /*
    |--------------------------------------------------------------------------
    | Confirm User Creation
    |--------------------------------------------------------------------------
    */

        if (strtolower($command) === "confirm") {
            if (session()->has("pending_user_create")) {
                return $this->confirmUserCreate($command);
            }

            if (session()->has("pending_user_update")) {
                return $this->confirmUserUpdate($command);
            }
            if (session()->has("pending_user_delete")) {
                return $this->confirmUserDelete($command);
            }
            if (session()->has("pending_user_restore")) {
                return $this->confirmUserRestore($command);
            }
            if (session()->has("pending_user_status")) {
                return $this->confirmUserStatus($command);
            }

            return [
                "success" => false,

                "message" => "No pending user action found.",
            ];
        }

        return [
            "success" => false,

            "message" => "User command not recognized.",
        ];
    }

    private function parseCreateUser(string $command): array
    {
        preg_match(
            '/user\s+named\s+(.*?)\s+email\s+(.*?)\s+password\s+(.*?)\s+in\s+company\s+(.*?)\s+department\s+(.*?)\s+role\s+(.*?)(?:\s+status\s+(active|inactive))?$/i',
            $command,
            $matches
        );

        return [
            "name" => trim($matches[1] ?? ""),

            "email" => trim($matches[2] ?? ""),

            "password" => trim($matches[3] ?? ""),

            "company_name" => trim($matches[4] ?? ""),

            "department_name" => trim($matches[5] ?? ""),
            "status" => isset($matches[7])
                ? (strtolower(trim($matches[7])) === "active"
                    ? 1
                    : 0)
                : 1,

            "role_name" => trim($matches[6] ?? ""),
        ];
    }
    //User creation confirmation
    private function requestUserCreateConfirmation(string $command): array
    {
        /*
    |--------------------------------------------------------------------------
    | Permission Validation
    |--------------------------------------------------------------------------
    */

        if (
            !auth()->check() ||
            !auth()
                ->user()
                ->can("users.create")
        ) {
            return [
                "success" => false,
                "message" => "You do not have permission to create users.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Parse Command
    |--------------------------------------------------------------------------
    */

        $data = $this->parseCreateUser($command);

        /*
    |--------------------------------------------------------------------------
    | Mandatory Fields Validation
    |--------------------------------------------------------------------------
    */

        if (
            empty($data["name"]) ||
            empty($data["email"]) ||
            empty($data["password"]) ||
            empty($data["company_name"]) ||
            empty($data["department_name"]) ||
            empty($data["role_name"])
        ) {
            return [
                "success" => false,
                "message" =>
                    "Name, email, password, company, department and role are required.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Company Validation
    |--------------------------------------------------------------------------
    */

        $company = $this->userService->findCompany($data["company_name"]);

        if (!$company) {
            return [
                "success" => false,
                "message" => "Company not found.",
            ];
        }

        /*
|--------------------------------------------------------------------------
| Company Status Validation
|--------------------------------------------------------------------------
*/

        if (!$company->status) {
            return [
                "success" => false,
                "message" => "Company {$company->name} is inactive. User cannot be created.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Company Admin Restriction
    |--------------------------------------------------------------------------
    */

        if (
            auth()
                ->user()
                ->hasRole("Company Admin") &&
            auth()->user()->company_id != $company->id
        ) {
            return [
                "success" => false,
                "message" => "You can only manage users of your own company.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Department Validation
    |--------------------------------------------------------------------------
    */

        $department = $this->userService->findDepartment(
            $company->id,
            $data["department_name"]
        );

        if (!$department) {
            return [
                "success" => false,
                "message" => "Department not found.",
            ];
        }

        /*
|--------------------------------------------------------------------------
| Department Status Validation
|--------------------------------------------------------------------------
*/

        if (!$department->status) {
            return [
                "success" => false,
                "message" => "Department {$department->name} is inactive. User cannot be created.",
            ];
        }
        /*
    |--------------------------------------------------------------------------
    | Role Validation
    |--------------------------------------------------------------------------
    */

        $role = $this->userService->findRole($data["role_name"]);

        if (!$role) {
            return [
                "success" => false,
                "message" => "Role not found.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Duplicate Email Validation
    |--------------------------------------------------------------------------
    */

        if ($this->userService->emailExists($data["email"])) {
            return [
                "success" => false,
                "message" => "Email already exists.",
            ];
        }
        $deletedUser = $this->userService->findDeletedUserByEmail(
            $data["email"]
        );

        if ($deletedUser) {
            return [
                "success" => false,

                "message" => "User {$data["email"]} is deleted. Please restore the user instead.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Store Pending Action
    |--------------------------------------------------------------------------
    */

        session([
            "pending_user_create" => [
                "name" => $data["name"],

                "email" => $data["email"],

                "password" => $data["password"],

                "company_id" => $company->id,

                "company_name" => $company->name,

                "department_id" => $department->id,

                "department_name" => $department->name,

                "role" => $role->name,
                "status" => $data["status"],
            ],
        ]);

        /*
    |--------------------------------------------------------------------------
    | Confirmation Message
    |--------------------------------------------------------------------------
    */

        return [
            "success" => true,

            "message" =>
                "⚠️ USER CREATION CONFIRMATION\n\n" .
                "Name: {$data["name"]}\n" .
                "Email: {$data["email"]}\n" .
                "Company: {$company->name}\n" .
                "Department: {$department->name}\n" .
                "Role: {$role->name}\n\n" .
                "Status: " .
                ($data["status"] ? "Active" : "Inactive") .
                "\n" .
                "Type confirm to continue.",
        ];
    }
    private function confirmUserCreate(string $command): array
    {
        if (!session()->has("pending_user_create")) {
            return [
                "success" => false,

                "message" => "No pending user creation found.",
            ];
        }

        $pending = session("pending_user_create");

        try {
            $user = $this->userService->createUser([
                "name" => $pending["name"],

                "email" => $pending["email"],

                "password" => $pending["password"],

                "company_id" => $pending["company_id"],

                "department_id" => $pending["department_id"],

                "role" => $pending["role"],

                "status" => $pending["status"] ?? 1,
            ]);

            session()->forget("pending_user_create");

            return [
                "success" => true,

                "message" => "✅ User {$user->name} created successfully.",
            ];
        } catch (\Exception $e) {
            return [
                "success" => false,

                "message" => $e->getMessage(),
            ];
        }
    }
    private function parseUpdateUser(string $command): array
    {
        $data = [
            "email" => "",
            "field" => "",
            "value" => "",
        ];

        preg_match(
            '/update\s+user\s+([^\s]+)\s+(name|email|role|department|company|status)\s+(.+)$/i',
            $command,
            $matches
        );

        if (!empty($matches)) {
            $data["email"] = trim($matches[1]);

            $data["field"] = strtolower(trim($matches[2]));

            $data["value"] = trim($matches[3]);
        }

        return $data;
    }
    private function parseDeleteUser(string $command): array
    {
        $data = [
            "email" => "",
        ];

        preg_match('/delete\s+user\s+([^\s]+)$/i', $command, $matches);

        if (!empty($matches)) {
            $data["email"] = trim($matches[1]);
        }

        return $data;
    }
    private function parseUserStatus(string $command): array
    {
        $data = [
            "email" => "",

            "status" => "",
        ];

        preg_match(
            '/(activate|deactivate)\s+user\s+([^\s]+)$/i',
            $command,
            $matches
        );

        if (!empty($matches)) {
            $data["status"] = strtolower(trim($matches[1]));

            $data["email"] = trim($matches[2]);
        }

        return $data;
    }
    private function requestUserStatusConfirmation(string $command): array
    {
        /*
    |--------------------------------------------------------------------------
    | Permission Validation
    |--------------------------------------------------------------------------
    */

        if (!auth()->check()) {
            return [
                "success" => false,
                "message" => "Please login first.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Parse Command
    |--------------------------------------------------------------------------
    */

        $data = $this->parseUserStatus($command);

        if (
            $data["status"] === "activate" &&
            !auth()
                ->user()
                ->can("users.activate")
        ) {
            return [
                "success" => false,
                "message" => "You do not have permission to activate users.",
            ];
        }

        if (
            $data["status"] === "deactivate" &&
            !auth()
                ->user()
                ->can("users.deactivate")
        ) {
            return [
                "success" => false,
                "message" => "You do not have permission to deactivate users.",
            ];
        }

        if (empty($data["email"]) || empty($data["status"])) {
            return [
                "success" => false,

                "message" => "Invalid user status command.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Find User
    |--------------------------------------------------------------------------
    */

        $user = $this->userService->findUserByEmail($data["email"]);

        if (!$user) {
            return [
                "success" => false,

                "message" => "User not found.",
            ];
        }

        //Check Status
        if ($data["status"] === "activate" && $user->status == 1) {
            return [
                "success" => false,

                "message" => "User {$user->name} is already active.",
            ];
        }

        if ($data["status"] === "deactivate" && $user->status == 0) {
            return [
                "success" => false,

                "message" => "User {$user->name} is already inactive.",
            ];
        }
        /*
    |--------------------------------------------------------------------------
    | Self Protection
    |--------------------------------------------------------------------------
    */

        if (auth()->id() == $user->id) {
            return [
                "success" => false,

                "message" => "You cannot change your own status.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Company Admin Restriction
    |--------------------------------------------------------------------------
    */

        if (
            !auth()
                ->user()
                ->hasRole("Super Admin") &&
            auth()->user()->company_id != $user->company_id
        ) {
            return [
                "success" => false,

                "message" => "You can only manage users of your own company.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Super Admin Protection
    |--------------------------------------------------------------------------
    */

        if (
            $user->hasRole("Super Admin") &&
            !auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            return [
                "success" => false,

                "message" => "Super Admin status cannot be changed.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Current Status
    |--------------------------------------------------------------------------
    */

        $currentStatus = $user->status ? "Active" : "Inactive";

        $newStatus = $data["status"] === "activate" ? "Active" : "Inactive";

        /*
    |--------------------------------------------------------------------------
    | Store Pending Action
    |--------------------------------------------------------------------------
    */

        session([
            "pending_user_status" => [
                "user_id" => $user->id,

                "name" => $user->name,

                "email" => $user->email,

                "current_status" => $currentStatus,

                "new_status" => $newStatus,
            ],
        ]);

        /*
    |--------------------------------------------------------------------------
    | Confirmation Message
    |--------------------------------------------------------------------------
    */

        return [
            "success" => true,

            "message" =>
                "⚠️ USER STATUS UPDATE CONFIRMATION\n\n" .
                "Name: {$user->name}\n" .
                "Email: {$user->email}\n" .
                "Current Status: {$currentStatus}\n" .
                "New Status: {$newStatus}\n\n" .
                "Type confirm to continue.",
        ];
    }

    private function confirmUserStatus(string $command): array
    {
        /*
    |--------------------------------------------------------------------------
    | Pending Action Validation
    |--------------------------------------------------------------------------
    */

        if (!session()->has("pending_user_status")) {
            return [
                "success" => false,

                "message" => "No pending user status update found.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Authentication Check
    |--------------------------------------------------------------------------
    */

        if (!auth()->check()) {
            return [
                "success" => false,

                "message" => "Please login first.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Get Pending Data
    |--------------------------------------------------------------------------
    */

        $pending = session("pending_user_status");

        /*
    |--------------------------------------------------------------------------
    | Find User
    |--------------------------------------------------------------------------
    */

        $user = $this->userService->findUserById($pending["user_id"]);

        if (!$user) {
            return [
                "success" => false,

                "message" => "User not found.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Permission Re-check
    |--------------------------------------------------------------------------
    */

        if (
            $pending["new_status"] === "Active" &&
            !auth()
                ->user()
                ->can("users.activate")
        ) {
            return [
                "success" => false,

                "message" => "You do not have permission to activate users.",
            ];
        }

        if (
            $pending["new_status"] === "Inactive" &&
            !auth()
                ->user()
                ->can("users.deactivate")
        ) {
            return [
                "success" => false,

                "message" => "You do not have permission to deactivate users.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Self Protection
    |--------------------------------------------------------------------------
    */

        if (auth()->id() === $user->id) {
            return [
                "success" => false,

                "message" => "You cannot change your own status.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Company Restriction
    |--------------------------------------------------------------------------
    */

        if (
            auth()
                ->user()
                ->hasRole("Company Admin") &&
            $user->company_id != auth()->user()->company_id
        ) {
            return [
                "success" => false,

                "message" => "You can only manage users of your own company.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Super Admin Protection
    |--------------------------------------------------------------------------
    */

        if (
            $user->hasRole("Super Admin") &&
            !auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            return [
                "success" => false,

                "message" => "Super Admin status cannot be changed.",
            ];
        }

        try {
            /*
        |--------------------------------------------------------------------------
        | Update Status Through UserService
        |--------------------------------------------------------------------------
        */

            $updatedUser = $this->userService->toggleStatus($user);

            /*
        |--------------------------------------------------------------------------
        | Verify Requested Status
        |--------------------------------------------------------------------------
        */

            $expectedStatus = $pending["new_status"] === "Active" ? 1 : 0;

            if ($updatedUser->status != $expectedStatus) {
                return [
                    "success" => false,

                    "message" => "Status update failed.",
                ];
            }

            /*
        |--------------------------------------------------------------------------
        | Clear Pending Action
        |--------------------------------------------------------------------------
        */

            session()->forget("pending_user_status");

            return [
                "success" => true,

                "message" => "✅ User {$updatedUser->name} status updated successfully.",
            ];
        } catch (\Exception $e) {
            return [
                "success" => false,

                "message" => $e->getMessage(),
            ];
        }
    }
    private function requestUserDeleteConfirmation(string $command): array
    {
        /*
    |--------------------------------------------------------------------------
    | Permission Validation
    |--------------------------------------------------------------------------
    */

        if (
            !auth()->check() ||
            !auth()
                ->user()
                ->can("users.delete")
        ) {
            return [
                "success" => false,

                "message" => "You do not have permission to delete users.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Parse Command
    |--------------------------------------------------------------------------
    */

        $data = $this->parseDeleteUser($command);

        if (empty($data["email"])) {
            return [
                "success" => false,

                "message" => "Invalid delete user command.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | User Validation
    |--------------------------------------------------------------------------
    */

        $user = $this->userService->findUserByEmail($data["email"]);

        if (!$user) {
            return [
                "success" => false,

                "message" => "User not found.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Self Delete Protection
    |--------------------------------------------------------------------------
    */

        if (auth()->id() == $user->id) {
            return [
                "success" => false,

                "message" => "You cannot delete your own account.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Company Admin Restriction
    |--------------------------------------------------------------------------
    */

        if (
            !auth()
                ->user()
                ->hasRole("Super Admin") &&
            auth()->user()->company_id != $user->company_id
        ) {
            return [
                "success" => false,

                "message" => "You can only manage users of your own company.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Super Admin Protection
    |--------------------------------------------------------------------------
    */

        if (
            $user->hasRole("Super Admin") &&
            !auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            return [
                "success" => false,

                "message" => "Super Admin accounts cannot be deleted.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Store Pending Delete Action
    |--------------------------------------------------------------------------
    */

        session([
            "pending_user_delete" => [
                "user_id" => $user->id,

                "name" => $user->name,

                "email" => $user->email,

                "company" => $user->company?->name ?? "No Company",

                "role" => $user->roles->first()?->name ?? "No Role",
            ],
        ]);

        /*
    |--------------------------------------------------------------------------
    | Confirmation Message
    |--------------------------------------------------------------------------
    */

        return [
            "success" => true,

            "message" =>
                "⚠️ USER DELETE CONFIRMATION\n\n" .
                "Name: {$user->name}\n" .
                "Email: {$user->email}\n" .
                "Company: {$user->company?->name}\n" .
                "Role: {$user->roles->first()?->name}\n\n" .
                "Type confirm to continue.",
        ];
    }

    private function confirmUserDelete(string $command): array
    {
        /*
    |--------------------------------------------------------------------------
    | Pending Action Validation
    |--------------------------------------------------------------------------
    */

        if (!session()->has("pending_user_delete")) {
            return [
                "success" => false,

                "message" => "No pending user delete found.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Authentication Check
    |--------------------------------------------------------------------------
    */

        if (!auth()->check()) {
            return [
                "success" => false,

                "message" => "Please login first.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Permission Re-check
    |--------------------------------------------------------------------------
    */

        if (
            !auth()
                ->user()
                ->can("users.delete")
        ) {
            return [
                "success" => false,

                "message" => "You do not have permission to delete users.",
            ];
        }

        $pending = session("pending_user_delete");

        /*
    |--------------------------------------------------------------------------
    | Find User
    |--------------------------------------------------------------------------
    */

        $user = $this->userService->findUserById($pending["user_id"]);

        if (!$user) {
            return [
                "success" => false,

                "message" => "User not found.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Self Protection
    |--------------------------------------------------------------------------
    */

        if (auth()->id() === $user->id) {
            return [
                "success" => false,

                "message" => "You cannot delete your own account.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Company Restriction
    |--------------------------------------------------------------------------
    */

        if (
            auth()
                ->user()
                ->hasRole("Company Admin") &&
            $user->company_id != auth()->user()->company_id
        ) {
            return [
                "success" => false,

                "message" => "You can only delete users of your own company.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Super Admin Protection
    |--------------------------------------------------------------------------
    */

        if (
            $user->hasRole("Super Admin") &&
            !auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            return [
                "success" => false,

                "message" => "Super Admin accounts cannot be deleted.",
            ];
        }

        try {
            /*
        |--------------------------------------------------------------------------
        | Delete Through Service
        |--------------------------------------------------------------------------
        */

            $this->userService->deleteUser($user);

            /*
        |--------------------------------------------------------------------------
        | Clear Pending Action
        |--------------------------------------------------------------------------
        */

            session()->forget("pending_user_delete");

            return [
                "success" => true,

                "message" => "✅ User {$pending["name"]} deleted successfully.",
            ];
        } catch (\Exception $e) {
            return [
                "success" => false,

                "message" => $e->getMessage(),
            ];
        }
    }

    private function confirmUserUpdate(string $command): array
    {
        if (!session()->has("pending_user_update")) {
            return [
                "success" => false,
                "message" => "No pending user update found.",
            ];
        }

        if (!auth()->check()) {
            return [
                "success" => false,
                "message" => "Please login first.",
            ];
        }

        if (
            !auth()
                ->user()
                ->can("users.edit")
        ) {
            return [
                "success" => false,
                "message" => "You do not have permission to update users.",
            ];
        }

        $pending = session("pending_user_update");

        $user = $this->userService->findUserById($pending["user_id"]);

        if (!$user) {
            return [
                "success" => false,
                "message" => "User not found.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Super Admin Protection
    |--------------------------------------------------------------------------
    */

        if (
            $user->hasRole("Super Admin") &&
            !auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            return [
                "success" => false,
                "message" => "Super Admin account cannot be modified.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Self Protection
    |--------------------------------------------------------------------------
    */

        if (
            auth()->id() == $user->id &&
            in_array($pending["field"], ["role", "status", "company", "email"])
        ) {
            return [
                "success" => false,
                "message" =>
                    "You cannot modify your own role, status, company or email.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Company Restriction
    |--------------------------------------------------------------------------
    */

        if (
            auth()
                ->user()
                ->hasRole("Company Admin") &&
            $user->company_id != auth()->user()->company_id
        ) {
            return [
                "success" => false,
                "message" => "You can only manage users of your own company.",
            ];
        }

        $updateData = [];

        try {
            switch ($pending["field"]) {
                case "name":
                    $updateData["name"] = $pending["value"];

                    break;

                case "email":
                    if (
                        $this->userService->emailExistsForOtherUser(
                            $pending["value"],
                            $user->id
                        )
                    ) {
                        return [
                            "success" => false,
                            "message" => "Email already exists.",
                        ];
                    }

                    $updateData["email"] = $pending["value"];

                    break;

                case "status":
                    $status = strtolower($pending["value"]);

                    if (!in_array($status, ["active", "inactive"])) {
                        return [
                            "success" => false,
                            "message" => "Invalid status value.",
                        ];
                    }

                    $updateData["status"] = $status === "active" ? 1 : 0;

                    break;

                case "role":
                    if (
                        !auth()
                            ->user()
                            ->can("users.assign_role")
                    ) {
                        return [
                            "success" => false,
                            "message" => "You cannot assign roles.",
                        ];
                    }

                    $role = $this->userService->findRole($pending["value"]);

                    if (!$role) {
                        return [
                            "success" => false,
                            "message" => "Role not found.",
                        ];
                    }

                    if (
                        $role->name === "Super Admin" &&
                        !auth()
                            ->user()
                            ->hasRole("Super Admin")
                    ) {
                        return [
                            "success" => false,
                            "message" =>
                                "Only Super Admin can assign Super Admin.",
                        ];
                    }

                    $updateData["role"] = $role->name;

                    break;

                case "department":
                    $department = $this->userService->findDepartment(
                        $user->company_id,
                        $pending["value"]
                    );

                    if (!$department) {
                        return [
                            "success" => false,
                            "message" => "Department not found.",
                        ];
                    }

                    $updateData["department_id"] = $department->id;

                    $updateData["company_id"] = $user->company_id;

                    break;

                case "company":
                    if (
                        !auth()
                            ->user()
                            ->hasRole("Super Admin")
                    ) {
                        return [
                            "success" => false,
                            "message" => "Only Super Admin can change company.",
                        ];
                    }

                    $company = $this->userService->findCompany(
                        $pending["value"]
                    );

                    if (!$company) {
                        return [
                            "success" => false,
                            "message" => "Company not found.",
                        ];
                    }

                    $updateData["company_id"] = $company->id;

                    $updateData["department_id"] = null;

                    break;

                default:
                    return [
                        "success" => false,
                        "message" => "Unsupported update field.",
                    ];
            }

            $updatedUser = $this->userService->updateUser($user, $updateData);

            session()->forget("pending_user_update");

            return [
                "success" => true,

                "message" => "✅ User {$updatedUser->name} updated successfully.",
            ];
        } catch (\Exception $e) {
            return [
                "success" => false,

                "message" => $e->getMessage(),
            ];
        }
    }
    private function requestUserUpdateConfirmation(string $command): array
    {
        /*
    |--------------------------------------------------------------------------
    | Permission Validation
    |--------------------------------------------------------------------------
    */

        if (
            !auth()->check() ||
            !auth()
                ->user()
                ->can("users.edit")
        ) {
            return [
                "success" => false,

                "message" => "You do not have permission to update users.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Parse Command
    |--------------------------------------------------------------------------
    */

        $data = $this->parseUpdateUser($command);

        if (
            empty($data["email"]) ||
            empty($data["field"]) ||
            empty($data["value"])
        ) {
            return [
                "success" => false,

                "message" => "Invalid update command.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Status Validation
    |--------------------------------------------------------------------------
    */

        if (
            $data["field"] === "status" &&
            !in_array(strtolower($data["value"]), ["active", "inactive"])
        ) {
            return [
                "success" => false,

                "message" => "Status must be active or inactive.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | User Validation
    |--------------------------------------------------------------------------
    */

        $user = $this->userService->findUserByEmail($data["email"]);

        if (!$user) {
            return [
                "success" => false,

                "message" => "User not found.",
            ];
        }
        /*
|--------------------------------------------------------------------------
| Department Validation
|--------------------------------------------------------------------------
*/

        if ($data["field"] === "department") {
            $department = $this->userService->findDepartment(
                $user->company_id,
                $data["value"]
            );

            if (!$department) {
                return [
                    "success" => false,
                    "message" => "Department '{$data["value"]}' not found in company {$user->company?->name}.",
                ];
            }

            if (!$department->status) {
                return [
                    "success" => false,
                    "message" => "Department {$department->name} is inactive.",
                ];
            }

            if ($user->department_id == $department->id) {
                return [
                    "success" => false,
                    "message" => "User is already assigned to department {$department->name}.",
                ];
            }

            // Store exact matched department name
            $data["value"] = $department->name;
        }
        /*
    |--------------------------------------------------------------------------
    | Only Super Admin Can Change Company
    |--------------------------------------------------------------------------
    */

        if (
            $data["field"] === "company" &&
            !auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            return [
                "success" => false,

                "message" => "Only Super Admin can change user company.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Company Admin Restrictions
    |--------------------------------------------------------------------------
    */

        if (
            auth()
                ->user()
                ->hasRole("Company Admin")
        ) {
            if (auth()->user()->company_id != $user->company_id) {
                return [
                    "success" => false,

                    "message" =>
                        "You can only manage users of your own company.",
                ];
            }

            if (
                $data["field"] === "role" &&
                !auth()
                    ->user()
                    ->can("users.assign_role")
            ) {
                return [
                    "success" => false,

                    "message" => "You do not have permission to assign roles.",
                ];
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Self Protection
    |--------------------------------------------------------------------------
    */

        if (
            auth()->id() === $user->id &&
            in_array($data["field"], ["role", "status", "company", "email"])
        ) {
            return [
                "success" => false,

                "message" =>
                    "You cannot modify your own role, status, company or email.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Validate New Value Before Confirmation
    |--------------------------------------------------------------------------
    */

        switch ($data["field"]) {
            case "role":
                $role = $this->userService->findRole($data["value"]);

                if (!$role) {
                    return [
                        "success" => false,

                        "message" => "Role '{$data["value"]}' not found.",
                    ];
                }

                break;

            case "company":
                $company = $this->userService->findCompany($data["value"]);

                if (!$company) {
                    return [
                        "success" => false,

                        "message" => "Company '{$data["value"]}' not found.",
                    ];
                }

                break;

            case "email":
                if (
                    $this->userService->emailExistsForOtherUser(
                        $data["value"],
                        $user->id
                    )
                ) {
                    return [
                        "success" => false,

                        "message" => "Email already exists.",
                    ];
                }

                break;
        }

        /*
    |--------------------------------------------------------------------------
    | Current Value
    |--------------------------------------------------------------------------
    */

        $currentValue = "";

        switch ($data["field"]) {
            case "name":
                $currentValue = $user->name;

                break;

            case "role":
                $currentValue = $user->roles->first()?->name ?? "No Role";

                break;

            case "department":
                $currentValue = $user->department?->name ?? "No Department";

                break;

            case "company":
                $currentValue = $user->company?->name ?? "No Company";

                break;

            case "status":
                $currentValue = $user->status ? "Active" : "Inactive";

                break;

            case "email":
                $currentValue = $user->email;

                break;
        }

        /*
    |--------------------------------------------------------------------------
    | No Change Validation
    |--------------------------------------------------------------------------
    */

        if (
            strtolower(trim($currentValue)) === strtolower(trim($data["value"]))
        ) {
            return [
                "success" => false,

                "message" => "New value is same as current value.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Store Pending Action
    |--------------------------------------------------------------------------
    */

        session([
            "pending_user_update" => [
                "user_id" => $user->id,

                "email" => $user->email,

                "field" => $data["field"],

                "value" => $data["value"],
            ],
        ]);

        /*
    |--------------------------------------------------------------------------
    | Confirmation Message
    |--------------------------------------------------------------------------
    */

        return [
            "success" => true,

            "message" =>
                "⚠️ USER UPDATE CONFIRMATION\n\n" .
                "User: {$user->name}\n" .
                "Email: {$user->email}\n" .
                "Field: {$data["field"]}\n" .
                "Current Value: {$currentValue}\n" .
                "New Value: {$data["value"]}\n\n" .
                "Type confirm to continue.",
        ];
    }
    private function parseShowUser(string $command): array
    {
        $data = [
            "email" => "",
        ];

        preg_match('/show\s+user\s+([^\s]+)$/i', $command, $matches);

        if (!empty($matches)) {
            $data["email"] = trim($matches[1]);
        }

        return $data;
    }
    private function showUser(string $command): array
    {
        $data = $this->parseShowUser($command);

        if (empty($data["email"])) {
            return [
                "success" => false,

                "message" => "Invalid show user command.",
            ];
        }

        $user = $this->userService->findUserByEmail($data["email"]);

        if (!$user) {
            return [
                "success" => false,

                "message" => "User not found.",
            ];
        }

        return [
            "success" => true,

            "message" =>
                "👤 USER DETAILS\n\n" .
                "Name: {$user->name}\n" .
                "Email: {$user->email}\n" .
                "Company: " .
                ($user->company?->name ?? "N/A") .
                "\n" .
                "Department: " .
                ($user->department?->name ?? "N/A") .
                "\n" .
                "Role: " .
                ($user->roles->first()?->name ?? "N/A") .
                "\n" .
                "Status: " .
                ($user->status ? "Active" : "Inactive"),
        ];
    }
    private function showUsersByStatus(int $status): array
    {
        if (!auth()->check()) {
            return [
                "success" => false,

                "message" => "Please login first.",
            ];
        }

        $companyId = null;

        if (
            auth()
                ->user()
                ->hasRole("Company Admin")
        ) {
            $companyId = auth()->user()->company_id;
        }

        $users = $this->userService->getUsersByStatus($status, $companyId);

        if ($users->isEmpty()) {
            return [
                "success" => false,

                "message" => $status
                    ? "No active users found."
                    : "No inactive users found.",
            ];
        }

        $message = $status ? "👥 ACTIVE USERS\n\n" : "👥 INACTIVE USERS\n\n";

        foreach ($users as $user) {
            $message .=
                "Name: {$user->name}\n" .
                "Email: {$user->email}\n" .
                "Company: " .
                ($user->company?->name ?? "N/A") .
                "\n" .
                "Department: " .
                ($user->department?->name ?? "N/A") .
                "\n" .
                "Role: " .
                ($user->roles->first()?->name ?? "N/A") .
                "\n\n";
        }

        $message .= "Total Users: " . $users->count();

        return [
            "success" => true,

            "message" => $message,
        ];
    }
    private function parseRestoreUser(string $command): array
    {
        $data = [
            "email" => "",
        ];

        preg_match('/restore\s+user\s+([^\s]+)$/i', $command, $matches);

        if (!empty($matches)) {
            $data["email"] = trim($matches[1]);
        }

        return $data;
    }
    private function requestUserRestoreConfirmation(string $command): array
    {
        /*
    |--------------------------------------------------------------------------
    | Permission Validation
    |--------------------------------------------------------------------------
    */

        if (
            !auth()->check() ||
            !auth()
                ->user()
                ->can("users.restore")
        ) {
            return [
                "success" => false,

                "message" => "You do not have permission to restore users.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Parse Command
    |--------------------------------------------------------------------------
    */

        $data = $this->parseRestoreUser($command);

        if (empty($data["email"])) {
            return [
                "success" => false,

                "message" => "Invalid restore user command.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Find Deleted User
    |--------------------------------------------------------------------------
    */

        $user = $this->userService->findDeletedUserByEmail($data["email"]);

        if (!$user) {
            return [
                "success" => false,

                "message" => "Deleted user not found.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Company Admin Restriction
    |--------------------------------------------------------------------------
    */

        if (
            auth()
                ->user()
                ->hasRole("Company Admin") &&
            $user->company_id != auth()->user()->company_id
        ) {
            return [
                "success" => false,

                "message" => "You can only restore users of your own company.",
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Store Pending Action
    |--------------------------------------------------------------------------
    */

        session([
            "pending_user_restore" => [
                "user_id" => $user->id,

                "name" => $user->name,

                "email" => $user->email,

                "company_id" => $user->company_id,
            ],
        ]);

        /*
    |--------------------------------------------------------------------------
    | Confirmation Message
    |--------------------------------------------------------------------------
    */

        return [
            "success" => true,

            "message" =>
                "⚠️ USER RESTORE CONFIRMATION\n\n" .
                "Name: {$user->name}\n" .
                "Email: {$user->email}\n\n" .
                "Type confirm to continue.",
        ];
    }
    private function confirmUserRestore(string $command): array
    {
        if (!session()->has("pending_user_restore")) {
            return [
                "success" => false,

                "message" => "No pending user restore found.",
            ];
        }

        $pending = session("pending_user_restore");

        $user = $this->userService->findDeletedUserById($pending["user_id"]);

        if (!$user) {
            return [
                "success" => false,

                "message" => "User not found.",
            ];
        }

        $this->userService->restoreUser($user);

        session()->forget("pending_user_restore");

        return [
            "success" => true,

            "message" => "✅ User {$user->name} restored successfully.",
        ];
    }
}
