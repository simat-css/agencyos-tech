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
        'departments' => function ($query) {

            $query->latest();

            // Department Search
            if (request('search')) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . request('search') . '%')
                      ->orWhere('code', 'like', '%' . request('search') . '%');
                });
            }

            // Status Filter
            if (request()->filled('status')) {
                $query->where('status', request('status'));
            }
        }
    ]);

    // Non Super Admin
    if (!auth()->user()->hasRole('Super Admin')) {

        $companyQuery->where(
            'id',
            auth()->user()->company_id
        );

    } else {

        // Company Filter
        if (request('company')) {

            $companyQuery->where(
                'id',
                request('company')
            );

        }

    }

    // Only companies having departments
    $companyQuery->whereHas('departments', function ($query) {

        if (request('search')) {

            $query->where(function ($q) {

                $q->where(
                    'name',
                    'like',
                    '%' . request('search') . '%'
                )
                ->orWhere(
                    'code',
                    'like',
                    '%' . request('search') . '%'
                );

            });

        }

        if (request()->filled('status')) {

            $query->where(
                'status',
                request('status')
            );

        }

    });

    $companiesWithDepartments = $companyQuery
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return view('departments.index', [

        'companiesWithDepartments' => $companiesWithDepartments,

       'companies' => auth()->user()->hasRole('Super Admin')

    ? Company::active()
        ->whereHas('departments')
        ->orderBy('name')
        ->get()

    : Company::active()
        ->where('id', auth()->user()->company_id)
        ->whereHas('departments')
        ->orderBy('name')
        ->get(),

        'totalDepartments' => Department::count(),

        'activeDepartments' => Department::where(
            'status',
            1
        )->count(),

        'inactiveDepartments' => Department::where(
            'status',
            0
        )->count(),

        'hasCompany' => Company::exists(),

    ]);
}
    /**
     * Create Form
     */
    public function create(Request $request)
    {
        if (auth()->user()->hasRole('Super Admin')) {
            $companies = Company::active()
                ->orderBy('name')
                ->get();
        } else {
            $companies = Company::active()
                ->where('id', auth()->user()->company_id)
                ->get();
        }

        $selectedCompany = $request->company;

        return view(
            'departments.create',
            compact('companies', 'selectedCompany')
        );
    }

    /**
     * Store Department
     */
    public function store(StoreDepartmentRequest $request)
    {
        $department = $this->departmentService->create(
            $request->validated()
        );

        activity()
            ->causedBy(Auth::user())
            ->performedOn($department)
            ->log('Department created successfully.');

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Department Details
     */
    public function show(Department $department)
    {
        $department->load([
            'company',
            'creator',
            'updater',
        ]);

        return view(
            'departments.show',
            compact('department')
        );
    }

    /**
     * Edit Form
     */
    public function edit(Department $department)
    {
        if (auth()->user()->hasRole('Super Admin')) {
            $companies = Company::active()
                ->orderBy('name')
                ->get();
        } else {
            $companies = Company::active()
                ->where('id', auth()->user()->company_id)
                ->get();
        }

        return view(
            'departments.edit',
            compact('department', 'companies')
        );
    }

    /**
     * Update Department
     */
    public function update(
        UpdateDepartmentRequest $request,
        Department $department
    ) {
        $this->departmentService->update(
            $department,
            $request->validated()
        );

        activity()
            ->causedBy(Auth::user())
            ->performedOn($department)
            ->log('Department updated successfully.');

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department updated successfully.');
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
            return response()->json([
                'message' => $message
            ], 400);
        }

        return redirect()
            ->back()
            ->with('error', $message);
    }

    $this->departmentService->delete($department);

    activity()
        ->causedBy(Auth::user())
        ->performedOn($department)
        ->log('Department deleted successfully.');

    $message = 'Department deleted successfully.';

    if (request()->expectsJson()) {
        return response()->json([
            'message' => $message
        ]);
    }

    return redirect()
        ->route('departments.index')
        ->with('success', $message);
}

    /**
     * Toggle Status
     */
    public function toggleStatus(Department $department)
    {
        $department->load('company');

        if (
            !$department->status &&
            $department->company &&
            $department->company->status == 0
        ) {
            return response()->json([
                'message' => 'Cannot activate department because company is inactive.'
            ], 400);
        }

        $department->update([
            'status' => $department->status ? 0 : 1
        ]);

        activity()
    ->causedBy(Auth::user())
    ->performedOn($department)
    ->log(
        $department->status
            ? 'Department activated successfully.'
            : 'Department deactivated successfully.'
    );

        return response()->json([
            'message' => 'Department status updated successfully'
        ]);
    }

   public function bulkAction(Request $request)
{
    $request->validate([
        'action' => 'required|in:activate,deactivate,delete',
        'ids'    => 'required|array|min:1',
    ]);

    $departments = Department::whereIn(
        'id',
        $request->ids
    );

    /*
    |--------------------------------------------------------------------------
    | Delete Departments
    |--------------------------------------------------------------------------
    */
    if ($request->action === 'delete') {

        $selectedDepartments = $departments->get();

        foreach ($selectedDepartments as $department) {

            if ($department->users()->exists()) {

                return response()->json([
                    'message' =>
                        "Department '{$department->name}' cannot be deleted because users are assigned to it."
                ], 400);
            }

            activity()
                ->causedBy(Auth::user())
                ->performedOn($department)
                ->log('Department deleted successfully.');
        }

        $departments->delete();

        return response()->json([
            'message' => 'Departments deleted successfully'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Activate Departments
    |--------------------------------------------------------------------------
    */
    if ($request->action === 'activate') {

        $selectedDepartments = $departments
            ->with('company')
            ->get();

        foreach ($selectedDepartments as $department) {

            if ($department->status) {

                return response()->json([
                    'message' =>
                        "Department '{$department->name}' is already active."
                ], 400);
            }

            if (
                $department->company &&
                !$department->company->status
            ) {
                return response()->json([
                    'message' =>
                        "Cannot activate '{$department->name}' because its company is inactive."
                ], 400);
            }
        }

        $departments->update([
            'status' => 1,
            'updated_by' => Auth::id(),
        ]);

        foreach ($selectedDepartments as $department) {

            activity()
                ->causedBy(Auth::user())
                ->performedOn($department)
                ->log('Department activated successfully.');
        }

        return response()->json([
            'message' => 'Departments activated successfully'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Deactivate Departments
    |--------------------------------------------------------------------------
    */
    if ($request->action === 'deactivate') {

        $selectedDepartments = $departments->get();

        foreach ($selectedDepartments as $department) {

            if (!$department->status) {

                return response()->json([
                    'message' =>
                        "Department '{$department->name}' is already inactive."
                ], 400);
            }
        }

        $departments->update([
            'status' => 0,
            'updated_by' => Auth::id(),
        ]);

        foreach ($selectedDepartments as $department) {

            activity()
                ->causedBy(Auth::user())
                ->performedOn($department)
                ->log('Department deactivated successfully.');
        }

        return response()->json([
            'message' => 'Departments deactivated successfully'
        ]);
    }

    return response()->json([
        'message' => 'Invalid action'
    ], 400);
}
}