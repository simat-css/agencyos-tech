<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Company;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    protected DepartmentService $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public function index()
    {
        $companyQuery = Company::with([
            "departments" => function ($query) {
                $query->latest();

                // Department Search
                if (request("search")) {
                    $query->where(function ($q) {
                        $q->where(
                            "name",
                            "like",
                            "%" . request("search") . "%"
                        )->orWhere(
                            "code",
                            "like",
                            "%" . request("search") . "%"
                        );
                    });
                }

                // Status Filter
                if (request()->filled("status")) {
                    $query->where("status", request("status"));
                }
            },
        ]);

        // Non Super Admin
        if (
            !auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            $companyQuery->where("id", auth()->user()->company_id);
        } else {
            // Company Filter
            if (request("company")) {
                $companyQuery->where("id", request("company"));
            }
        }

        // Only companies having departments
        $companyQuery->whereHas("departments", function ($query) {
            if (request("search")) {
                $query->where(function ($q) {
                    $q->where(
                        "name",
                        "like",
                        "%" . request("search") . "%"
                    )->orWhere("code", "like", "%" . request("search") . "%");
                });
            }

            if (request()->filled("status")) {
                $query->where("status", request("status"));
            }
        });

        $companiesWithDepartments = $companyQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view("departments.index", [
            "companiesWithDepartments" => $companiesWithDepartments,

            "companies" => auth()
                ->user()
                ->hasRole("Super Admin")
                ? Company::active()
                    ->whereHas("departments")
                    ->orderBy("name")
                    ->get()
                : Company::active()
                    ->where("id", auth()->user()->company_id)
                    ->whereHas("departments")
                    ->orderBy("name")
                    ->get(),

            "totalDepartments" => Department::count(),

            "activeDepartments" => Department::where("status", 1)->count(),

            "inactiveDepartments" => Department::where("status", 0)->count(),

            "hasCompany" => Company::exists(),
        ]);
    }
    /**
     * Create Form
     */
    public function create(Request $request)
    {
        if (
            auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            $companies = Company::active()
                ->orderBy("name")
                ->get();
        } else {
            $companies = Company::active()
                ->where("id", auth()->user()->company_id)
                ->get();
        }

        $selectedCompany = $request->company;

        return view(
            "departments.create",
            compact("companies", "selectedCompany")
        );
    }

    /**
     * Store Department
     */
    public function store(StoreDepartmentRequest $request)
    {
        $department = $this->departmentService->create($request->validated());

        // activity()
        //     ->causedBy(Auth::user())
        //     ->performedOn($department)
        //     ->log("Department created successfully.");

        return redirect()
            ->route("departments.index")
            ->with("success", "Department created successfully.");
    }

    /**
     * Department Details
     */
    public function show(Department $department)
    {
        $department->load(["company", "creator", "updater"]);

        return view("departments.show", compact("department"));
    }

    /**
     * Edit Form
     */
    public function edit(Department $department)
    {
        if (
            auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            $companies = Company::active()
                ->orderBy("name")
                ->get();
        } else {
            $companies = Company::active()
                ->where("id", auth()->user()->company_id)
                ->get();
        }

        return view("departments.edit", compact("department", "companies"));
    }

    /**
     * Update Department
     */
    public function update(
        UpdateDepartmentRequest $request,
        Department $department
    ) {
        $this->departmentService->update($department, $request->validated());

        // activity()
        //     ->causedBy(Auth::user())
        //     ->performedOn($department)
        //     ->log("Department updated successfully.");

        return redirect()
            ->route("departments.index")
            ->with("success", "Department updated successfully.");
    }

    /**
     * Delete Department
     */
    public function destroy(Department $department)
    {
        // Check Assigned Users
        if ($department->users()->exists()) {
            $message = "Cannot delete department '{$department->name}' because users are assigned to it.";

            if (request()->expectsJson()) {
                return response()->json(
                    [
                        "message" => $message,
                    ],
                    400
                );
            }

            return redirect()
                ->back()
                ->with("error", $message);
        }

        $this->departmentService->delete($department);

        // activity()
        //     ->causedBy(Auth::user())
        //     ->performedOn($department)
        //     ->log("Department deleted successfully.");

        $message = "Department deleted successfully.";

        if (request()->expectsJson()) {
            return response()->json([
                "message" => $message,
            ]);
        }

        return redirect()
            ->route("departments.index")
            ->with("success", $message);
    }

    /**
     * Toggle Status
     */
    public function toggleStatus(Department $department)
    {
        $department->load("company");

        if (
            !$department->status &&
            $department->company &&
            $department->company->status == 0
        ) {
            return response()->json(
                [
                    "message" =>
                        "Cannot activate department because company is inactive.",
                ],
                400
            );
        }

        // $department->update([
        //     "status" => $department->status ? 0 : 1,
        // ]);
        $department = $this->departmentService->toggleStatus($department);

        // activity()
        //     ->causedBy(Auth::user())
        //     ->performedOn($department)
        //     ->log(
        //         $department->status
        //             ? "Department activated successfully."
        //             : "Department deactivated successfully."
        //     );

        return response()->json([
            "message" => "Department status updated successfully",
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            "action" => "required|in:activate,deactivate,delete",
            "ids" => "required|array|min:1",
        ]);

        $departments = Department::whereIn("id", $request->ids);

        /*
    |--------------------------------------------------------------------------
    | Delete Departments
    |--------------------------------------------------------------------------
    */
        if ($request->action === "delete") {
            $selectedDepartments = $departments->get();

            $deletedCount = 0;

            $deletedDepartments = [];

            $skippedDepartments = [];

            foreach ($selectedDepartments as $department) {
                if ($department->users()->exists()) {
                    $skippedDepartments[] =
                        $department->name . " (assigned users found)";

                    continue;
                }

                // activity()
                //     ->causedBy(Auth::user())
                //     ->performedOn($department)
                //     ->log("Department deleted successfully.");

                $department->delete();

                $deletedDepartments[] = $department->name;

                $deletedCount++;
            }

            $message = "";

            if ($deletedCount > 0) {
                $message = "{$deletedCount} department(s) deleted successfully.";

                if (!empty($deletedDepartments)) {
                    $message .=
                        "\n\nDeleted: " . implode(", ", $deletedDepartments);
                }

                if (!empty($skippedDepartments)) {
                    $message .=
                        "\n\nSkipped: " . implode(", ", $skippedDepartments);
                }
            } else {
                $message = "No department was deleted.";

                if (!empty($skippedDepartments)) {
                    $message .=
                        "\n\nSkipped: " . implode(", ", $skippedDepartments);
                }
            }

            return response()->json(
                [
                    "success" => $deletedCount > 0,

                    "deleted" => $deletedCount,

                    "skipped" => count($skippedDepartments),

                    "message" => $message,

                    "deleted_departments" => $deletedDepartments,

                    "skipped_departments" => $skippedDepartments,
                ],
                $deletedCount > 0 ? 200 : 422
            );
        }
        /*
    |--------------------------------------------------------------------------
    | Activate Departments
    |--------------------------------------------------------------------------
    */
        if ($request->action === "activate") {
            $selectedDepartments = $departments->with("company")->get();

            $activatedCount = 0;

            $alreadyActive = [];

            $inactiveCompany = [];

            foreach ($selectedDepartments as $department) {
                if ($department->status) {
                    $alreadyActive[] = $department->name;

                    continue;
                }

                if ($department->company && !$department->company->status) {
                    $inactiveCompany[] = $department->name;

                    continue;
                }

                $department->update([
                    "status" => 1,
                    "updated_by" => Auth::id(),
                ]);

                // activity()
                //     ->causedBy(Auth::user())
                //     ->performedOn($department)
                //     ->log("Department activated successfully.");

                $activatedCount++;
            }

            $message = "";

            if ($activatedCount > 0) {
                $message .= "{$activatedCount} department(s) activated successfully.";
            }

            if (!empty($alreadyActive)) {
                $message .=
                    " Already active: " . implode(", ", $alreadyActive) . ".";
            }

            if (!empty($inactiveCompany)) {
                $message .=
                    " Company inactive: " .
                    implode(", ", $inactiveCompany) .
                    ".";
            }

            return response()->json(
                [
                    "success" => $activatedCount > 0,
                    "message" => $message ?: "No department activated.",
                ],
                $activatedCount > 0 ? 200 : 422
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Deactivate Departments
    |--------------------------------------------------------------------------
    */
        if ($request->action === "deactivate") {
            $result = $this->departmentService->bulkDeactivate($request->ids);

            return response()->json(
                [
                    "success" => $result["deactivated"] > 0,

                    "message" =>
                        $result["deactivated"] .
                        " department(s) deactivated successfully." .
                        (!empty($result["alreadyInactive"])
                            ? " Already inactive: " .
                                implode(", ", $result["alreadyInactive"]) .
                                "."
                            : ""),
                ],
                $result["deactivated"] > 0 ? 200 : 422
            );
            $selectedDepartments = $departments->get();

            $deactivatedCount = 0;

            $alreadyInactive = [];

            foreach ($selectedDepartments as $department) {
                if (!$department->status) {
                    $alreadyInactive[] = $department->name;

                    continue;
                }

                $department->update([
                    "status" => 0,
                    "updated_by" => Auth::id(),
                ]);

                // activity()
                //     ->causedBy(Auth::user())
                //     ->performedOn($department)
                //     ->log("Department deactivated successfully.");

                $deactivatedCount++;
            }

            $message = "";

            if ($deactivatedCount > 0) {
                $message .= "{$deactivatedCount} department(s) deactivated successfully.";
            }

            if (!empty($alreadyInactive)) {
                $message .=
                    " Already inactive: " .
                    implode(", ", $alreadyInactive) .
                    ".";
            }

            return response()->json(
                [
                    "success" => $deactivatedCount > 0,
                    "message" => $message ?: "No department deactivated.",
                ],
                $deactivatedCount > 0 ? 200 : 422
            );
        }

        return response()->json(
            [
                "message" => "Invalid action",
            ],
            400
        );
    }
}
