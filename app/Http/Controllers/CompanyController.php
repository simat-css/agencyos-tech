<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
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
            auth()
                ->user()
                ->hasRole("Company Admin")
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
            auth()
                ->user()
                ->hasRole("Company Admin")
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
            $company->toArray()
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
        $oldData = $company->toArray();
        $this->companyService->update($company, $request->validated());

        ActivityHelper::log(
            Auth::user(),
            $company,
            "company",
            "updated",
            $oldData,
            $company->fresh()->toArray()
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
        $oldData = $company->toArray();
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
        $oldData = $company->toArray();
        $this->companyService->toggleStatus($company);
        ActivityHelper::log(
            Auth::user(),
            $company,
            "company",
            "status_changed",
            $oldData,
            $company->fresh()->toArray()
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

                foreach ($companies as $company) {
                    if (!$company->status) {
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
                    }
                }

                break;

            case "deactivate":
                $companies = Company::whereIn("id", $request->ids)->get();

                foreach ($companies as $company) {
                    if ($company->status) {
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
                    }
                }

                break;

            case "delete":
                $companies = Company::whereIn("id", $request->ids)->get();

                foreach ($companies as $company) {
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
                }

                break;
        }

        return response()->json([
            "success" => true,
            "message" => "Action completed successfully.",
        ]);
    }
}
