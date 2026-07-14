<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Department;
use App\Services\UserService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /*
    |--------------------------------------------------------------------------
    | Users Listing
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $query = User::with(["company", "department", "roles"]);

        if (
            auth()
                ->user()
                ->hasRole("Company Admin")
        ) {
            $query->where("company_id", auth()->user()->company_id);
        }

        if (request("search")) {
            $search = request("search");

            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")

                    ->orWhere("email", "like", "%{$search}%")

                    ->orWhereHas("company", function ($company) use ($search) {
                        $company->where("name", "like", "%{$search}%");
                    })

                    ->orWhereHas("department", function ($department) use (
                        $search
                    ) {
                        $department->where("name", "like", "%{$search}%");
                    })

                    ->orWhereHas("roles", function ($role) use ($search) {
                        $role->where("name", "like", "%{$search}%");
                    });
            });
        }

        if (
            request("company") &&
            !auth()
                ->user()
                ->hasRole("Company Admin")
        ) {
            $query->where("company_id", request("company"));
        }

        if (request("department")) {
            $query->where("department_id", request("department"));
        }

        if (request()->filled("status")) {
            $query->where("status", request("status"));
        }

        $users = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Companies
        |--------------------------------------------------------------------------
        */

        if (
            auth()
                ->user()
                ->hasRole("Company Admin")
        ) {
            $companies = Company::where(
                "id",
                auth()->user()->company_id
            )->get();
        } else {
            $companies = Company::active()
                ->orderBy("name")
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Departments
        |--------------------------------------------------------------------------
        */

        if (
            auth()
                ->user()
                ->hasRole("Company Admin")
        ) {
            $departments = Department::active()
                ->where("company_id", auth()->user()->company_id)
                ->orderBy("name")
                ->get();
        } else {
            $departments = Department::active()
                ->orderBy("name")
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statsQuery = User::query();

        if (
            auth()
                ->user()
                ->hasRole("Company Admin")
        ) {
            $statsQuery->where("company_id", auth()->user()->company_id);
        }

        $totalUsers = (clone $statsQuery)->count();

        $activeUsers = (clone $statsQuery)->where("status", 1)->count();

        $inactiveUsers = (clone $statsQuery)->where("status", 0)->count();

        return view(
            "users.index",
            compact(
                "users",
                "companies",
                "departments",
                "totalUsers",
                "activeUsers",
                "inactiveUsers"
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create User Form
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        if (
            !auth()
                ->user()
                ->can("users.create")
        ) {
            abort(403);
        }

        if (
            auth()
                ->user()
                ->hasRole("Company Admin")
        ) {
            $companies = Company::where(
                "id",
                auth()->user()->company_id
            )->get();

            $departments = Department::active()
                ->where("company_id", auth()->user()->company_id)
                ->get();
        } else {
            $companies = Company::active()
                ->orderBy("name")
                ->get();

            $departments = Department::active()
                ->orderBy("name")
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Role Access
        |--------------------------------------------------------------------------
        */

        if (
            auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            $roles = Role::all();
        } else {
            $roles = Role::whereNotIn("name", ["Super Admin"])->get();
        }

        return view(
            "users.create",
            compact("companies", "departments", "roles")
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store User
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email",
            "password" => "required|min:8",
            "company_id" => "required|exists:companies,id",
            "department_id" => "nullable|exists:departments,id",
            "role" => "required|exists:roles,name",
            "status" => "required|boolean",
            "profile_photo" => "nullable|image|max:2048",
        ]);

        try {
            $this->userService->createUser($validated);

            return redirect()
                ->route("users.index")
                ->with("success", "User created successfully");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with("error", $e->getMessage());
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Edit User
    |--------------------------------------------------------------------------
    */

    public function edit(User $user)
    {
        if (
            !auth()
                ->user()
                ->can("users.edit")
        ) {
            abort(403);
        }

        if (
            auth()
                ->user()
                ->hasRole("Company Admin") &&
            $user->company_id != auth()->user()->company_id
        ) {
            abort(403);
        }

        if (
            $user->hasRole("Super Admin") &&
            !auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            abort(403);
        }

        if (
            auth()
                ->user()
                ->hasRole("Company Admin")
        ) {
            $companies = Company::where(
                "id",
                auth()->user()->company_id
            )->get();
        } else {
            $companies = Company::active()
                ->orderBy("name")
                ->get();
        }

        $departments = Department::active()

            ->where("company_id", $user->company_id)

            ->orderBy("name")

            ->get();

        if (
            auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            $roles = Role::all();
        } else {
            $roles = Role::whereNotIn("name", ["Super Admin"])->get();
        }

        return view(
            "users.edit",
            compact("user", "companies", "departments", "roles")
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update User
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, User $user)
    {
        if (
            !auth()
                ->user()
                ->can("users.edit")
        ) {
            abort(403);
        }

        if (
            auth()
                ->user()
                ->hasRole("Company Admin") &&
            $user->company_id != auth()->user()->company_id
        ) {
            abort(403);
        }

        if (
            $user->hasRole("Super Admin") &&
            !auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            abort(403);
        }

        $validated = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email," . $user->id,
            "company_id" => "required|exists:companies,id",
            "department_id" => "nullable|exists:departments,id",
            "role" => "required|exists:roles,name",
            "status" => "required|boolean",
            "profile_photo" => "nullable|image|max:2048",
            "password" => "nullable|min:8",
        ]);

        try {
            $this->userService->updateUser($user, $validated);

            return redirect()
                ->route("users.index")
                ->with("success", "User updated successfully");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with("error", $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Departments By Company
    |--------------------------------------------------------------------------
    */

    public function getDepartments(Company $company)
    {
        if (
            auth()
                ->user()
                ->hasRole("Company Admin") &&
            $company->id != auth()->user()->company_id
        ) {
            abort(403);
        }

        $departments = Department::active()
            ->where("company_id", $company->id)
            ->select("id", "name")
            ->orderBy("name")
            ->get();

        return response()->json($departments);
    }
    /*
    |--------------------------------------------------------------------------
    | Show User
    |--------------------------------------------------------------------------
    */

    public function show(User $user)
    {
        if (
            !auth()
                ->user()
                ->can("users.view")
        ) {
            abort(403);
        }

        if (
            auth()
                ->user()
                ->hasRole("Company Admin") &&
            $user->company_id != auth()->user()->company_id
        ) {
            abort(403);
        }

        if (
            $user->hasRole("Super Admin") &&
            !auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            abort(403);
        }

        $user->load(["company", "department", "roles"]);

        return view("users.show", compact("user"));
    }

    /*
    |--------------------------------------------------------------------------
    | Delete User
    |--------------------------------------------------------------------------
    */

    public function destroy(User $user)
    {
        if (
            !auth()
                ->user()
                ->can("users.delete")
        ) {
            abort(403);
        }

        if (
            auth()
                ->user()
                ->hasRole("Company Admin") &&
            $user->company_id != auth()->user()->company_id
        ) {
            abort(403);
        }

        if (
            $user->hasRole("Super Admin") &&
            !auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            abort(403);
        }

        try {
            $this->userService->deleteUser($user);

            return redirect()
                ->route("users.index")
                ->with("success", "User deleted successfully");
        } catch (\Exception $e) {
            return back()->with("error", $e->getMessage());
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Toggle User Status
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(User $user)
    {
        if (
            !auth()
                ->user()
                ->can("users.edit")
        ) {
            return response()->json(
                [
                    "success" => false,

                    "message" => "Permission denied.",
                ],
                403
            );
        }

        try {
            $result = $this->userService->toggleStatus($user);

            return response()->json([
                "success" => true,

                "status" => $result->status,

                "message" => $result->status
                    ? "User activated successfully"
                    : "User deactivated successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,

                    "message" => $e->getMessage(),
                ],
                403
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Action
    |--------------------------------------------------------------------------
    */

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            "action" => "required|in:activate,deactivate,delete",

            "ids" => "required|array|min:1",

            "ids.*" => "exists:users,id",
        ]);

        try {
            $this->userService->bulkAction($validated);

            return response()->json([
                "success" => true,

                "message" => "Action completed successfully.",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,

                    "message" => $e->getMessage(),
                ],
                403
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Export Users
    |--------------------------------------------------------------------------
    */

    public function export()
    {
        if (
            !auth()
                ->user()
                ->can("users.export")
        ) {
            abort(403);
        }

        activity()
            ->causedBy(auth()->user())

            ->withProperties([
                "module" => "User",

                "action" => "export",
            ])

            ->log("Users exported");

        return Excel::download(
            new UsersExport(),

            "users.xlsx"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Import Users
    |--------------------------------------------------------------------------
    */

    public function import(Request $request)
    {
        if (
            !auth()
                ->user()
                ->can("users.import")
        ) {
            abort(403);
        }

        $request->validate([
            "file" => "required|mimes:xlsx,xls,csv",
        ]);

        try {
            $import = new UsersImport();

            Excel::import(
                $import,

                $request->file("file")
            );

            activity()
                ->causedBy(auth()->user())

                ->withProperties([
                    "module" => "User",

                    "action" => "import",

                    "imported" => $import->imported,

                    "skipped" => $import->skipped,
                ])

                ->log("Users imported");

            return back()->with(
                "success",

                $import->imported .
                    " users imported successfully. " .
                    $import->skipped .
                    " duplicate/invalid entries skipped."
            );
        } catch (\Exception $e) {
            return back()->with(
                "error",

                $e->getMessage()
            );
        }
    }
}
