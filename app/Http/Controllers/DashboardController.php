<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'companies' => Company::count(),
            'departments' => Department::count(),
            'users' => User::count(),
            'projects' => 0,
        ];


 $companyData = [
    'Jan' => Company::whereMonth('created_at',1)->count(),
    'Feb' => Company::whereMonth('created_at',2)->count(),
    'Mar' => Company::whereMonth('created_at',3)->count(),
    'Apr' => Company::whereMonth('created_at',4)->count(),
    'May' => Company::whereMonth('created_at',5)->count(),
    'Jun' => Company::whereMonth('created_at',6)->count(),
    'Jul' => Company::whereMonth('created_at',7)->count(),
    'Aug' => Company::whereMonth('created_at',8)->count(),
    'Sep' => Company::whereMonth('created_at',9)->count(),
    'Oct' => Company::whereMonth('created_at',10)->count(),
    'Nov' => Company::whereMonth('created_at',11)->count(),
    'Dec' => Company::whereMonth('created_at',12)->count(),
];


$roleData = collect([
    'Users'              => User::count(),
    'Departments'        => Department::count(),
    'Active Companies'   => Company::where('status', 1)->count(),
    'Inactive Companies' => Company::where('status', 0)->count(),
]);

$companyStatusData = collect([
    'Active Companies'   => Company::where('status', 1)->count(),
    'Inactive Companies' => Company::where('status', 0)->count(),
]);


        $activities = [
            'New user created',
            'Company added',
            'Department updated',
            'Profile changed'
        ];


        return view('dashboard',compact(
            'stats',
            'companyData',
            'roleData',
            'companyStatusData',
            'activities'
        ));
    }
}