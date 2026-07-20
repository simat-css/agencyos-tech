<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class ActivityLogController extends Controller
{
   public function index(Request $request)
{
    $authUser = auth()->user();


    /*
    |--------------------------------------------------------------------------
    | Company Users (Fallback for old logs)
    |--------------------------------------------------------------------------
    */

    $companyUserIds = collect();

    if (!$authUser->hasRole('Super Admin')) {

        $companyUserIds = User::where(
            'company_id',
            $authUser->company_id
        )
        ->pluck('id');
    }



    /*
    |--------------------------------------------------------------------------
    | Main Activity Logs Query
    |--------------------------------------------------------------------------
    */

    $query = Activity::with('causer')
        ->latest();


 if ($authUser->hasRole('Company Admin')) {

    $query->where(function ($q) use ($authUser, $companyUserIds) {

        $q->where(
            'properties->company_id',
            $authUser->company_id
        )

        ->orWhereIn(
            'causer_id',
            $companyUserIds
        );

    });

} elseif (!$authUser->hasRole('Super Admin')) {

    $query->where(
        'causer_id',
        $authUser->id
    );

}



    // User Filter
    if ($request->user_id) {

        $query->where(
            'causer_id',
            $request->user_id
        );
    }



    // Module Filter
    if ($request->module) {

        $query->whereJsonContains(
            'properties->module',
            $request->module
        );
    }



    // Action Filter
    if ($request->action) {

        $query->whereJsonContains(
            'properties->action',
            $request->action
        );
    }



    // Date Filter
    if ($request->date_from) {

        $query->whereDate(
            'created_at',
            '>=',
            $request->date_from
        );
    }


    if ($request->date_to) {

        $query->whereDate(
            'created_at',
            '<=',
            $request->date_to
        );
    }



    // Search
    if ($request->search) {

        $query->where(function ($q) use ($request) {

            $q->where(
                'description',
                'like',
                '%' . $request->search . '%'
            )
            ->orWhereHas('causer', function ($user) use ($request) {

                $user->where(
                    'name',
                    'like',
                    '%' . $request->search . '%'
                );

            });

        });
    }



    $logs = $query
        ->paginate(20)
        ->withQueryString();



    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    $statsQuery = Activity::query();


if ($authUser->hasRole('Company Admin')) {

    $statsQuery->where(function ($q) use ($authUser, $companyUserIds) {

        $q->where(
            'properties->company_id',
            $authUser->company_id
        )
        ->orWhereIn(
            'causer_id',
            $companyUserIds
        );

    });

} elseif (!$authUser->hasRole('Super Admin')) {

    $statsQuery->where(
        'causer_id',
        $authUser->id
    );

}



    $totalLogs = (clone $statsQuery)->count();


    $todayLogs = (clone $statsQuery)
        ->whereDate('created_at', today())
        ->count();


    $weekLogs = (clone $statsQuery)
        ->whereBetween(
            'created_at',
            [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]
        )
        ->count();


    $monthLogs = (clone $statsQuery)
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();



    $createdLogs = (clone $statsQuery)
        ->whereJsonContains(
            'properties->action',
            'created'
        )
        ->count();


    $updatedLogs = (clone $statsQuery)
        ->whereJsonContains(
            'properties->action',
            'updated'
        )
        ->count();


    $deletedLogs = (clone $statsQuery)
        ->whereJsonContains(
            'properties->action',
            'deleted'
        )
        ->count();


    $restoredLogs = (clone $statsQuery)
        ->whereJsonContains(
            'properties->action',
            'restored'
        )
        ->count();


    $statusLogs = (clone $statsQuery)
        ->whereJsonContains(
            'properties->action',
            'status_updated'
        )
        ->count();



    $bulkLogs = (clone $statsQuery)
        ->where(function ($q) {

            $q->whereJsonContains(
                'properties->action',
                'bulk_activate'
            )
            ->orWhereJsonContains(
                'properties->action',
                'bulk_deactivate'
            )
            ->orWhereJsonContains(
                'properties->action',
                'bulk_deleted'
            );

        })
        ->count();



    /*
    |--------------------------------------------------------------------------
    | Users Dropdown
    |--------------------------------------------------------------------------
    */

    $users = collect();

if ($authUser->hasRole('Super Admin')) {

    $users = User::select(
            'id',
            'name'
        )
        ->orderBy('name')
        ->get();

} elseif ($authUser->hasRole('Company Admin')) {

    $users = User::where(
            'company_id',
            $authUser->company_id
        )
        ->select(
            'id',
            'name'
        )
        ->orderBy('name')
        ->get();
}




    /*
    |--------------------------------------------------------------------------
    | Modules Dropdown
    |--------------------------------------------------------------------------
    */

    $moduleQuery = Activity::query();


    if ($authUser->hasRole('Company Admin')) {

    $moduleQuery->where(function ($q) use ($authUser, $companyUserIds) {

        $q->where(
            'properties->company_id',
            $authUser->company_id
        )
        ->orWhereIn(
            'causer_id',
            $companyUserIds
        );

    });

} elseif (!$authUser->hasRole('Super Admin')) {

    $moduleQuery->where(
        'causer_id',
        $authUser->id
    );

}


    $modules = $moduleQuery
        ->whereNotNull('properties')
        ->get()
        ->pluck('properties.module')
        ->filter()
        ->unique()
        ->values();



    /*
    |--------------------------------------------------------------------------
    | Actions Dropdown
    |--------------------------------------------------------------------------
    */

    $actionQuery = Activity::query();


    if ($authUser->hasRole('Company Admin')) {

    $actionQuery->where(function ($q) use ($authUser, $companyUserIds) {

        $q->where(
            'properties->company_id',
            $authUser->company_id
        )
        ->orWhereIn(
            'causer_id',
            $companyUserIds
        );

    });

} elseif (!$authUser->hasRole('Super Admin')) {

    $actionQuery->where(
        'causer_id',
        $authUser->id
    );

}


    $actions = $actionQuery
        ->whereNotNull('properties')
        ->get()
        ->pluck('properties.action')
        ->filter()
        ->unique()
        ->values();



    return view(
        'activity-logs.index',
        compact(
            'logs',
            'users',
            'modules',
            'actions',
            'totalLogs',
            'todayLogs',
            'weekLogs',
            'monthLogs',
            'createdLogs',
            'updatedLogs',
            'deletedLogs',
            'restoredLogs',
            'statusLogs',
            'bulkLogs'
        )
    );
}
public function destroy(Activity $activity)
{
    $activity->delete();

   return redirect()
    ->route('activity-logs.index')
    ->with('success', 'Activity log archived successfully.');
}
}