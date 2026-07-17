<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;

class DashboardController extends Controller
{

 public function index()
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Company Admin Dashboard
        |--------------------------------------------------------------------------
        */

        if ($user->hasRole('Company Admin')) {

            return $this->companyAdminDashboard();

        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin Dashboard
        |--------------------------------------------------------------------------
        */

        if ($user->hasRole('Super Admin')) {

            return $this->superAdminDashboard();

        }

        /*
        |--------------------------------------------------------------------------
        | All Other Users Dashboard
        |--------------------------------------------------------------------------
        */

        return $this->userDashboard();
    }
  private function superAdminDashboard()
    {
        $stats = [

            'companies'   => Company::count(),
            'departments' => Department::count(),
            'users'       => User::count(),
            'projects'    => 0,

        ];

        $companyData = [

            'Jan' => Company::whereMonth('created_at', 1)->count(),
            'Feb' => Company::whereMonth('created_at', 2)->count(),
            'Mar' => Company::whereMonth('created_at', 3)->count(),
            'Apr' => Company::whereMonth('created_at', 4)->count(),
            'May' => Company::whereMonth('created_at', 5)->count(),
            'Jun' => Company::whereMonth('created_at', 6)->count(),
            'Jul' => Company::whereMonth('created_at', 7)->count(),
            'Aug' => Company::whereMonth('created_at', 8)->count(),
            'Sep' => Company::whereMonth('created_at', 9)->count(),
            'Oct' => Company::whereMonth('created_at', 10)->count(),
            'Nov' => Company::whereMonth('created_at', 11)->count(),
            'Dec' => Company::whereMonth('created_at', 12)->count(),

        ];

        $roleData = collect([

            'Users'              => User::count(),
            'Departments'        => Department::count(),
            'Active Companies'   => Company::where('status', 1)->count(),
            'Inactive Companies' => Company::where('status', 0)->count(),

        ]);

        return view(
            'dashboard',
            compact(
                'stats',
                'companyData',
                'roleData'
            )
        );
    }

    //Admin Dashboard
private function companyAdminDashboard()
{
    $companyId = auth()->user()->company_id;

    $stats = [

        'employees' => User::where(
            'company_id',
            $companyId
        )->count(),

        'departments' => Department::where(
            'company_id',
            $companyId
        )->count(),

        'activeEmployees' => User::where(
            'company_id',
            $companyId
        )->where('status', 1)
         ->count(),

        'inactiveEmployees' => User::where(
            'company_id',
            $companyId
        )->where('status', 0)
         ->count(),

    ];

    /*
    |--------------------------------------------------------------------------
    | Employee Growth Chart
    |--------------------------------------------------------------------------
    */

    $employeeGrowthData = [

        'Jan' => User::where('company_id', $companyId)
            ->whereMonth('created_at', 1)
            ->count(),

        'Feb' => User::where('company_id', $companyId)
            ->whereMonth('created_at', 2)
            ->count(),

        'Mar' => User::where('company_id', $companyId)
            ->whereMonth('created_at', 3)
            ->count(),

        'Apr' => User::where('company_id', $companyId)
            ->whereMonth('created_at', 4)
            ->count(),

        'May' => User::where('company_id', $companyId)
            ->whereMonth('created_at', 5)
            ->count(),

        'Jun' => User::where('company_id', $companyId)
            ->whereMonth('created_at', 6)
            ->count(),

        'Jul' => User::where('company_id', $companyId)
            ->whereMonth('created_at', 7)
            ->count(),

        'Aug' => User::where('company_id', $companyId)
            ->whereMonth('created_at', 8)
            ->count(),

        'Sep' => User::where('company_id', $companyId)
            ->whereMonth('created_at', 9)
            ->count(),

        'Oct' => User::where('company_id', $companyId)
            ->whereMonth('created_at', 10)
            ->count(),

        'Nov' => User::where('company_id', $companyId)
            ->whereMonth('created_at', 11)
            ->count(),

        'Dec' => User::where('company_id', $companyId)
            ->whereMonth('created_at', 12)
            ->count(),
    ];

    /*
    |--------------------------------------------------------------------------
    | Employees By Department
    |--------------------------------------------------------------------------
    */

    $departmentData = Department::where(
            'company_id',
            $companyId
        )
        ->withCount('users')
        ->get();

    return view(
        'dashboard.company-admin',
        compact(
            'stats',
            'employeeGrowthData',
            'departmentData'
        )
    );
}
//User Dashboard
private function userDashboard()
{
    $user = auth()->user();

    $stats = [

        'notifications' => $user
            ->unreadNotifications()
            ->count(),

        'department' => optional(
            $user->department
        )->name ?? 'N/A',

        'role' => $user
            ->roles
            ->first()?->name ?? 'N/A',

    ];

    return view(
        'dashboard.user-dashboard',
        compact(
            'stats'
        )
    );
}
}