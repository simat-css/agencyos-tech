<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Department;
use App\Services\UserService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
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


public function index()
{
    $query = User::with([
        'company',
        'department',
        'roles'
    ]);

    // Company Admin can only see own company users
    if (auth()->user()->hasRole('Company Admin')) {

        $query->where(
            'company_id',
            auth()->user()->company_id
        );
    }

    // Search
    if (request('search')) {

        $search = request('search');

        $query->where(function ($q) use ($search) {

            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")

              ->orWhereHas('company', function ($company) use ($search) {
                  $company->where('name', 'like', "%{$search}%");
              })

              ->orWhereHas('department', function ($department) use ($search) {
                  $department->where('name', 'like', "%{$search}%");
              })

              ->orWhereHas('roles', function ($role) use ($search) {
                  $role->where('name', 'like', "%{$search}%");
              });

        });
    }

    // Company Filter (Super Admin only)
    if (
        request('company') &&
        !auth()->user()->hasRole('Company Admin')
    ) {

        $query->where(
            'company_id',
            request('company')
        );
    }

    // Department Filter
    if (request('department')) {

        $query->where(
            'department_id',
            request('department')
        );
    }

    // Status Filter
    if (request()->filled('status')) {

        $query->where(
            'status',
            request('status')
        );
    }

    $users = $query
        ->latest()
        ->paginate(10)
        ->withQueryString();

    /*
    |--------------------------------------------------------------------------
    | Companies Dropdown
    |--------------------------------------------------------------------------
    */
    if (auth()->user()->hasRole('Company Admin')) {

        $companies = Company::where(
            'id',
            auth()->user()->company_id
        )->get();

    } else {

        $companies = Company::active()
            ->orderBy('name')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Departments Dropdown
    |--------------------------------------------------------------------------
    */
    if (auth()->user()->hasRole('Company Admin')) {

        $departments = Department::active()
            ->where(
                'company_id',
                auth()->user()->company_id
            )
            ->orderBy('name')
            ->get();

    } else {

        $departments = Department::active()
            ->orderBy('name')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */
    if (auth()->user()->hasRole('Company Admin')) {

        $totalUsers = User::where(
            'company_id',
            auth()->user()->company_id
        )->count();

        $activeUsers = User::where(
            'company_id',
            auth()->user()->company_id
        )
        ->where('status', 1)
        ->count();

        $inactiveUsers = User::where(
            'company_id',
            auth()->user()->company_id
        )
        ->where('status', 0)
        ->count();

    } else {

        $totalUsers = User::count();

        $activeUsers = User::where(
            'status',
            1
        )->count();

        $inactiveUsers = User::where(
            'status',
            0
        )->count();
    }

    return view(
        'users.index',
        compact(
            'users',
            'companies',
            'departments',
            'totalUsers',
            'activeUsers',
            'inactiveUsers'
        )
    );
}



    public function create()
{
    if(auth()->user()->hasRole('Company Admin'))
    {
        $companies = Company::where(
            'id',
            auth()->user()->company_id
        )->get();

        $departments = Department::where(
            'company_id',
            auth()->user()->company_id
        )->get();
    }
    else
    {
        $companies = Company::where('status',1)
            ->get();

        $departments = Department::all();
    }

    $roles = Role::all();

    return view(
        'users.create',
        compact(
            'companies',
            'departments',
            'roles'
        )
    );
}



    public function store(Request $request)
    {

        $validated = $request->validate([

            'name'=>'required',
            'email'=>'required|email|unique:users',
            'password'=>'required|min:8',

            'company_id'    => 'required|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',

            'role'  => 'required',

            'status'=>'required',

            'profile_photo'=>'nullable|image'

        ]);


        $this->userService
            ->createUser($validated);


        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User created successfully'
            );
    }



public function edit(User $user)
{
    if (
        auth()->user()->hasRole('Company Admin') &&
        $user->company_id != auth()->user()->company_id
    ) {
        abort(403);
    }
    $companies = Company::active()->get();

    $departments = Department::where(
        'company_id',
        $user->company_id
    )
    ->where('status', 1)
    ->orderBy('name')
    ->get();

    $roles = Role::all();

    return view(
        'users.edit',
        compact(
            'user',
            'companies',
            'departments',
            'roles'
        )
    );
}

public function getDepartments(Company $company)
{
    $departments = Department::active()
        ->company($company->id)
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

    return response()->json($departments);
}

    public function update(Request $request, User $user)
    {

        $validated = $request->validate([

            'name'=>'required',

            'email'=>'required|email|
            unique:users,email,'.$user->id,

            'company_id'    => 'required|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',

            'role'  => 'required',
            'status'=>'required',

            'profile_photo'=>'nullable|image',

            'password'=>'nullable|min:8'

        ]);


        $this->userService
            ->updateUser(
                $user,
                $validated
            );


        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User updated successfully'
            );
    }

public function show(User $user)
{
    $user->load([
        'company',
        'department',
        'roles'
    ]);

    return view(
        'users.show',
        compact('user')
    );
}

    public function destroy(User $user)
    {

        $this->userService
            ->deleteUser($user);


        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User deleted successfully'
            );
    }

    public function toggleStatus(User $user)
{
    $user->status = !$user->status;
    $user->save();


    activity()
        ->causedBy(Auth::user())
        ->performedOn($user)
        ->log(
            $user->status
                ? 'User activated successfully'
                : 'User deactivated successfully'
        );


    return response()->json([
        'success' => true,
        'status' => $user->status,
        'message' => $user->status
            ? 'User activated successfully'
            : 'User deactivated successfully'
    ]);
}

public function bulkAction(Request $request)
{
    $request->validate([
        'action'=>'required|in:activate,deactivate,delete',
        'ids'=>'required|array|min:1'
    ]);


    switch($request->action)
    {

        case 'activate':

            User::whereIn('id',$request->ids)
            ->where('status',0)
            ->update([
                'status'=>1
            ]);

        break;


        case 'deactivate':

            User::whereIn('id',$request->ids)
            ->where('status',1)
            ->update([
                'status'=>0
            ]);

        break;


        case 'delete':

            User::whereIn('id',$request->ids)
            ->delete();

        break;

    }


    return response()->json([
        'success'=>true,
        'message'=>'Action completed successfully.'
    ]);
}

//Export Users
public function export()
{
    activity()
        ->causedBy(auth()->user())
        ->log('Users Exported');

    return Excel::download(
        new UsersExport(),
        'users.xlsx'
    );
}
//import Users
public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv'
    ]);


    $import = new UsersImport();


    Excel::import(
        $import,
        $request->file('file')
    );


    activity()
        ->causedBy(auth()->user())
        ->log('Users Imported');


    return back()->with(
        'success',
        $import->imported .
        ' users imported successfully. ' .
        $import->skipped .
        ' duplicate/invalid entries skipped.'
    );
}
}