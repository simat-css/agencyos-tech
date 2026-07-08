<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Services\CompanyService;
use function activity;
use Illuminate\Support\Facades\Auth;

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

    if (request('search')) {

        $query->where(function ($q) {

            $q->where('name', 'like', '%' . request('search') . '%')
              ->orWhere('email', 'like', '%' . request('search') . '%')
              ->orWhere('phone', 'like', '%' . request('search') . '%')
              ->orWhere('website', 'like', '%' . request('search') . '%');
        });
    }

    $companies = $query
        ->latest()
        ->paginate(10)
        ->withQueryString();

    $totalCompanies = Company::count();
    $activeCompanies = Company::where('status', 1)->count();
    $inactiveCompanies = Company::where('status', 0)->count();

    return view('companies.index', compact(
        'companies',
        'totalCompanies',
        'activeCompanies',
        'inactiveCompanies'
    ));
}

    /**
     * Create Form
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store Company
     */
    public function store(StoreCompanyRequest $request)
{
    $company = $this->companyService->create(
        $request->validated()
    );

    activity()
        ->causedBy(Auth::user())
        ->performedOn($company)
        ->log('Company created successfully');

    return redirect()
        ->route('companies.index')
        ->with('success', 'Company created successfully.');
}

    /**
     * Show Company
     */
   public function show(Company $company)
{
    $company->load([
        'departments' => function ($query) {
            $query->latest();
        }
    ]);

    return view(
        'companies.show',
        compact('company')
    );
}

    /**
     * Edit Form
     */
    public function edit(Company $company)
    {
        return view(
            'companies.edit',
            compact('company')
        );
    }

    /**
     * Update Company
     */
    public function update(UpdateCompanyRequest $request,Company $company) {
    $this->companyService->update(
        $company,
        $request->validated()
    );

    activity()
        ->causedBy(Auth::user())
        ->performedOn($company)
        ->log('Company updated successfully');

    return redirect()
        ->route('companies.index')
        ->with('success', 'Company updated successfully.');
}

    /**
     * Delete Company
     */
    public function destroy(Company $company)
{
    $this->companyService->delete($company);

    activity()
        ->causedBy(Auth::user())
        ->performedOn($company)
        ->log('Company deleted successfully');

    return redirect()
        ->route('companies.index')
        ->with('success', 'Company deleted successfully.');
}
public function toggleStatus(Company $company)
{
    $this->companyService->toggleStatus($company);

    return redirect()
        ->back()
        ->with(
            'success',
            'Company status updated successfully.'
        );
}
}