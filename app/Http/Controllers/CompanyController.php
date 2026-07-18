<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use App\Services\CompanyService;
use function activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Helpers\ActivityHelper;

class CompanyController extends Controller
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;

        /*
        Future Permission Setup

        $this->middleware('permission:companies.view')
            ->only(['index']);

        $this->middleware('permission:companies.create')
            ->only(['create', 'store']);

        $this->middleware('permission:companies.edit')
            ->only(['edit', 'update']);

        $this->middleware('permission:companies.delete')
            ->only(['destroy']);
        */
    }

    /**
     * Company Listing
     */
    public function index()
    {
        $query = Company::query();
        if (
            !auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            $query->where("id", auth()->user()->company_id);
        }
        if (request("search")) {
            $query->where(function ($q) {
                $q->where("name", "like", "%" . request("search") . "%")
                    ->orWhere("email", "like", "%" . request("search") . "%")
                    ->orWhere("phone", "like", "%" . request("search") . "%")
                    ->orWhere("website", "like", "%" . request("search") . "%");
            });
        }

        $companies = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // $totalCompanies = Company::count();
        // $activeCompanies = Company::where('status', 1)->count();
        // $inactiveCompanies = Company::where('status', 0)->count();
        if (
            !auth()
                ->user()
                ->hasRole("Super Admin")
        ) {
            $totalCompanies = Company::where(
                "id",
                auth()->user()->company_id
            )->count();

            $activeCompanies = Company::where("id", auth()->user()->company_id)
                ->where("status", 1)
                ->count();

            $inactiveCompanies = Company::where(
                "id",
                auth()->user()->company_id
            )
                ->where("status", 0)
                ->count();
        } else {
            $totalCompanies = Company::count();

            $activeCompanies = Company::where("status", 1)->count();

            $inactiveCompanies = Company::where("status", 0)->count();
        }

        return view(
            "companies.index",
            compact(
                "companies",
                "totalCompanies",
                "activeCompanies",
                "inactiveCompanies"
            )
        );
    }

    /**
     * Create Form
     */
    public function create()
    {
        return view("companies.create");
    }

    /**
     * Store Company
     */
    public function store(StoreCompanyRequest $request)
    {
        $company = $this->companyService->create($request->validated());

        ActivityHelper::log(
            Auth::user(),
            $company,
            "company",
            "created",
            [],
            [
                "name" => $company->name,
                "email" => $company->email,
                "phone" => $company->phone,
                "website" => $company->website,
                "status" => $company->status ? "Active" : "Inactive",
            ]
        );

        return redirect()
            ->route("companies.index")
            ->with("success", "Company created successfully.");
    }

    /**
     * Show Company
     */
    public function show(Company $company)
    {
        $company->load([
            "departments" => function ($query) {
                $query->latest();
            },
        ]);

        return view("companies.show", compact("company"));
    }

    /**
     * Edit Form
     */
    public function edit(Company $company)
    {
        return view("companies.edit", compact("company"));
    }

    /**
     * Update Company
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $oldData = $company->only([
            "name",
            "email",
            "phone",
            "website",
            "status",
        ]);

        $this->companyService->update($company, $request->validated());

        $newData = $company
            ->fresh()
            ->only(["name", "email", "phone", "website", "status"]);

        $oldValues = [];
        $newValues = [];

        foreach ($newData as $field => $value) {
            if (($oldData[$field] ?? null) != $value) {
                $oldValues[$field] = $oldData[$field];

                $newValues[$field] = $value;
            }
        }

        ActivityHelper::log(
            Auth::user(),
            $company->fresh(),
            "company",
            "updated",
            $oldValues,
            $newValues
        );

        return redirect()
            ->route("companies.index")
            ->with("success", "Company updated successfully.");
    }

    /**
     * Delete Company
     */
    public function destroy(Company $company)
    {
        $hasUsers = $company
            ->departments()
            ->whereHas("users", function ($query) use ($company) {
                $query->where("company_id", $company->id);
            })
            ->exists();

        if ($hasUsers) {
            return redirect()
                ->route("companies.index")
                ->with(
                    "error",
                    "Cannot delete company '{$company->name}'. One or more departments contain assigned users."
                );
        }

        $oldData = [
            "name" => $company->name,
            "email" => $company->email,
            "phone" => $company->phone,
            "website" => $company->website,
            "status" => $company->status ? "Active" : "Inactive",
        ];

        $this->companyService->delete($company);

        ActivityHelper::log(
            Auth::user(),
            $company,
            "company",
            "deleted",
            $oldData,
            []
        );

        return redirect()
            ->route("companies.index")
            ->with("success", "Company deleted successfully.");
    }
    public function toggleStatus(Company $company)
    {
        $oldData = [
            "status" => $company->status ? "Active" : "Inactive",
        ];

        $this->companyService->toggleStatus($company);

        $newData = [
            "status" => $company->fresh()->status ? "Active" : "Inactive",
        ];

        ActivityHelper::log(
            Auth::user(),
            $company->fresh(),
            "company",
            "status_changed",
            $oldData,
            $newData
        );

        return redirect()
            ->back()
            ->with("success", "Company status updated successfully.");
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            "action" => "required|in:activate,deactivate,delete",
            "ids" => "required|array|min:1",
        ]);

        switch ($request->action) {
            case "activate":
                $companies = Company::whereIn("id", $request->ids)->get();

                $activated = 0;
                $alreadyActive = 0;

                foreach ($companies as $company) {
                    if ($company->status) {
                        $alreadyActive++;
                        continue;
                    }

                    $oldData = $company->toArray();

                    $company->update([
                        "status" => 1,
                        "updated_by" => Auth::id(),
                    ]);

                    // Departments intentionally inactive rahenge

                    ActivityHelper::log(
                        Auth::user(),
                        $company,
                        "company",
                        "bulk_activated",
                        $oldData,
                        $company->fresh()->toArray()
                    );

                    $this->companyService->sendBulkNotification(
                        "bulk activated",
                        $company
                    );

                    $activated++;
                }

                if ($activated === 0) {
                    return response()->json(
                        [
                            "success" => false,
                            "message" =>
                                "All selected companies are already active.",
                        ],
                        422
                    );
                }

                $message = "{$activated} company(s) activated.";

                if ($alreadyActive > 0) {
                    $message .= " {$alreadyActive} already active.";
                }

                return response()->json([
                    "success" => true,
                    "message" => $message,
                ]);

            case "deactivate":
                $companies = Company::whereIn("id", $request->ids)->get();

                $deactivated = 0;
                $alreadyInactive = 0;

                foreach ($companies as $company) {
                    if (!$company->status) {
                        $alreadyInactive++;
                        continue;
                    }

                    $oldData = $company->toArray();

                    $company->update([
                        "status" => 0,
                        "updated_by" => Auth::id(),
                    ]);

                    $company
                        ->departments()
                        ->where("status", 1)
                        ->update([
                            "status" => 0,
                            "auto_deactivated" => true,
                            "updated_by" => Auth::id(),
                        ]);

                    User::where("company_id", $company->id)
                        ->where("status", 1)
                        ->update([
                            "status" => 0,
                        ]);

                    ActivityHelper::log(
                        Auth::user(),
                        $company,
                        "company",
                        "bulk_deactivated",
                        $oldData,
                        $company->fresh()->toArray()
                    );

                    $this->companyService->sendBulkNotification(
                        "bulk deactivated",
                        $company
                    );

                    $deactivated++;
                }

                if ($deactivated === 0) {
                    return response()->json(
                        [
                            "success" => false,
                            "message" =>
                                "All selected companies are already inactive.",
                        ],
                        422
                    );
                }

                $message = "{$deactivated} company(s) deactivated.";

                if ($alreadyInactive > 0) {
                    $message .= " {$alreadyInactive} already inactive.";
                }

                return response()->json([
                    "success" => true,
                    "message" => $message,
                ]);

            case "delete":
                $companies = Company::whereIn("id", $request->ids)->get();

                $deletedCount = 0;

                $deletedCompanies = [];

                $skippedCompanies = [];

                foreach ($companies as $company) {
                    $hasUsers = $company
                        ->departments()
                        ->whereHas("users", function ($query) use ($company) {
                            $query->where("company_id", $company->id);
                        })
                        ->exists();

                    if ($hasUsers) {
                        $skippedCompanies[] =
                            $company->name . " (assigned users found)";

                        continue;
                    }

                    $oldData = $company->toArray();

                    $this->companyService->delete($company);

                    ActivityHelper::log(
                        Auth::user(),
                        $company,
                        "company",
                        "bulk_deleted",
                        $oldData,
                        []
                    );

                    $deletedCompanies[] = $company->name;

                    $deletedCount++;
                }

                $message = "";

                if ($deletedCount > 0) {
                    $message = "{$deletedCount} company(s) deleted successfully.";

                    if (!empty($deletedCompanies)) {
                        $message .=
                            "\n\nDeleted: " . implode(", ", $deletedCompanies);
                    }

                    if (!empty($skippedCompanies)) {
                        $message .=
                            "\n\nSkipped: " . implode(", ", $skippedCompanies);
                    }
                } else {
                    $message = "No company was deleted.";

                    if (!empty($skippedCompanies)) {
                        $message .=
                            "\n\nSkipped: " . implode(", ", $skippedCompanies);
                    }
                }

                return response()->json(
                    [
                        "success" => $deletedCount > 0,

                        "deleted" => $deletedCount,

                        "skipped" => count($skippedCompanies),

                        "message" => $message,

                        "deleted_companies" => $deletedCompanies,

                        "skipped_companies" => $skippedCompanies,
                    ],
                    $deletedCount > 0 ? 200 : 422
                );
        }

        return response()->json(
            [
                "success" => false,
                "message" => "Invalid action.",
            ],
            422
        );
    }
}
