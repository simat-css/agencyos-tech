<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

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

        if ($user->hasRole("Company Admin")) {
            return $this->companyAdminDashboard();
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin Dashboard
        |--------------------------------------------------------------------------
        */

        if ($user->hasRole("Super Admin")) {
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
            "companies" => Company::count(),
            "departments" => Department::count(),
            "users" => User::count(),
            "projects" => 0,
        ];

        $companyData = [
            "Jan" => Company::whereMonth("created_at", 1)->count(),
            "Feb" => Company::whereMonth("created_at", 2)->count(),
            "Mar" => Company::whereMonth("created_at", 3)->count(),
            "Apr" => Company::whereMonth("created_at", 4)->count(),
            "May" => Company::whereMonth("created_at", 5)->count(),
            "Jun" => Company::whereMonth("created_at", 6)->count(),
            "Jul" => Company::whereMonth("created_at", 7)->count(),
            "Aug" => Company::whereMonth("created_at", 8)->count(),
            "Sep" => Company::whereMonth("created_at", 9)->count(),
            "Oct" => Company::whereMonth("created_at", 10)->count(),
            "Nov" => Company::whereMonth("created_at", 11)->count(),
            "Dec" => Company::whereMonth("created_at", 12)->count(),
        ];

        $roleData = collect([
            "Users" => User::count(),
            "Departments" => Department::count(),
            "Active Companies" => Company::where("status", 1)->count(),
            "Inactive Companies" => Company::where("status", 0)->count(),
        ]);

        $recentActivities = Activity::latest()
            ->take(5)
            ->get();

        $recentNotifications = auth()
            ->user()
            ->notifications()
            ->latest()
            ->take(5)
            ->get();

        return view(
            "dashboard",
            compact(
                "stats",
                "companyData",
                "roleData",
                "recentActivities",
                "recentNotifications"
            )
        );
    }

    //Admin Dashboard
    private function companyAdminDashboard()
    {
        $companyId = auth()->user()->company_id;

        $stats = [
            "employees" => User::where("company_id", $companyId)->count(),

            "departments" => Department::where(
                "company_id",
                $companyId
            )->count(),

            "activeEmployees" => User::where("company_id", $companyId)
                ->where("status", 1)
                ->count(),

            "inactiveEmployees" => User::where("company_id", $companyId)
                ->where("status", 0)
                ->count(),
        ];

        /*
    |--------------------------------------------------------------------------
    | Employee Growth Chart
    |--------------------------------------------------------------------------
    */

        $employeeGrowthData = [
            "Jan" => User::where("company_id", $companyId)
                ->whereMonth("created_at", 1)
                ->count(),

            "Feb" => User::where("company_id", $companyId)
                ->whereMonth("created_at", 2)
                ->count(),

            "Mar" => User::where("company_id", $companyId)
                ->whereMonth("created_at", 3)
                ->count(),

            "Apr" => User::where("company_id", $companyId)
                ->whereMonth("created_at", 4)
                ->count(),

            "May" => User::where("company_id", $companyId)
                ->whereMonth("created_at", 5)
                ->count(),

            "Jun" => User::where("company_id", $companyId)
                ->whereMonth("created_at", 6)
                ->count(),

            "Jul" => User::where("company_id", $companyId)
                ->whereMonth("created_at", 7)
                ->count(),

            "Aug" => User::where("company_id", $companyId)
                ->whereMonth("created_at", 8)
                ->count(),

            "Sep" => User::where("company_id", $companyId)
                ->whereMonth("created_at", 9)
                ->count(),

            "Oct" => User::where("company_id", $companyId)
                ->whereMonth("created_at", 10)
                ->count(),

            "Nov" => User::where("company_id", $companyId)
                ->whereMonth("created_at", 11)
                ->count(),

            "Dec" => User::where("company_id", $companyId)
                ->whereMonth("created_at", 12)
                ->count(),
        ];

        /*
    |--------------------------------------------------------------------------
    | Employees By Department
    |--------------------------------------------------------------------------
    */

        $departmentData = Department::where("company_id", $companyId)
            ->withCount("users")
            ->get();

        $recentActivities = Activity::whereHas("causer", function ($query) use (
            $companyId
        ) {
            $query->where("company_id", $companyId);
        })
            ->latest()
            ->take(5)
            ->get();
        $recentNotifications = auth()
            ->user()
            ->notifications()
            ->latest()
            ->take(5)
            ->get();

        return view(
            "dashboard.company-admin",
            compact(
                "stats",
                "employeeGrowthData",
                "departmentData",
                "recentActivities",
                "recentNotifications"
            )
        );
    }
    //User Dashboard
    private function userDashboard()
    {
        $user = auth()->user();

        $stats = [
            "notifications" => $user->unreadNotifications()->count(),

            "department" => optional($user->department)->name ?? "N/A",

            "role" => $user->roles->first()?->name ?? "N/A",
        ];

        /*
    |--------------------------------------------------------------------------
    | Activity Trend
    |--------------------------------------------------------------------------
    */

        $activityData = [
            "Jan" => Activity::where("causer_id", $user->id)
                ->whereMonth("created_at", 1)
                ->count(),
            "Feb" => Activity::where("causer_id", $user->id)
                ->whereMonth("created_at", 2)
                ->count(),
            "Mar" => Activity::where("causer_id", $user->id)
                ->whereMonth("created_at", 3)
                ->count(),
            "Apr" => Activity::where("causer_id", $user->id)
                ->whereMonth("created_at", 4)
                ->count(),
            "May" => Activity::where("causer_id", $user->id)
                ->whereMonth("created_at", 5)
                ->count(),
            "Jun" => Activity::where("causer_id", $user->id)
                ->whereMonth("created_at", 6)
                ->count(),
            "Jul" => Activity::where("causer_id", $user->id)
                ->whereMonth("created_at", 7)
                ->count(),
            "Aug" => Activity::where("causer_id", $user->id)
                ->whereMonth("created_at", 8)
                ->count(),
            "Sep" => Activity::where("causer_id", $user->id)
                ->whereMonth("created_at", 9)
                ->count(),
            "Oct" => Activity::where("causer_id", $user->id)
                ->whereMonth("created_at", 10)
                ->count(),
            "Nov" => Activity::where("causer_id", $user->id)
                ->whereMonth("created_at", 11)
                ->count(),
            "Dec" => Activity::where("causer_id", $user->id)
                ->whereMonth("created_at", 12)
                ->count(),
        ];

        /*
    |--------------------------------------------------------------------------
    | Notification Chart
    |--------------------------------------------------------------------------
    */

        $notificationData = collect([
            "Read" => $user
                ->notifications()
                ->whereNotNull("read_at")
                ->count(),

            "Unread" => $user->unreadNotifications()->count(),
        ]);

        /*
    |--------------------------------------------------------------------------
    | Recent Activities
    |--------------------------------------------------------------------------
    */

        $activities = Activity::where("causer_id", $user->id)
            ->latest()
            ->take(5)
            ->pluck("description");

        return view(
            "dashboard.user-dashboard",
            compact("stats", "activityData", "notificationData", "activities")
        );
    }
}
