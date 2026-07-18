<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class ActivityLogController extends Controller
{
    public function index(Request $request)
{
    $query = Activity::with('causer')
        ->latest();


    // User Filter
    if ($request->user_id) {

        $query->where('causer_id', $request->user_id);

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

        $query->where(function($q) use($request){

            $q->where('description','like',
                '%'.$request->search.'%'
            )
            ->orWhereHas('causer',function($user) use($request){

                $user->where('name','like',
                    '%'.$request->search.'%'
                );

            });

        });

    }


    $logs = $query->paginate(20)
                  ->withQueryString();
    
                  
    $totalLogs = Activity::count();


$todayLogs = Activity::whereDate(
    'created_at',
    today()
)->count();


$weekLogs = Activity::whereBetween(
    'created_at',
    [
        now()->startOfWeek(),
        now()->endOfWeek()
    ]
)->count();


$monthLogs = Activity::whereMonth(
    'created_at',
    now()->month
)->whereYear(
    'created_at',
    now()->year
)->count();



$createdLogs = Activity::whereJsonContains(
    'properties->action',
    'created'
)->count();


$updatedLogs = Activity::whereJsonContains(
    'properties->action',
    'updated'
)->count();


$deletedLogs = Activity::whereJsonContains(
    'properties->action',
    'deleted'
)->count(); 

$restoredLogs = Activity::whereJsonContains(
    'properties->action',
    'restored'
)->count();

$statusLogs = Activity::whereJsonContains(
    'properties->action',
    'status_updated'
)->count();

$bulkLogs = Activity::where(function ($q) {
    $q->whereJsonContains('properties->action', 'bulk_activate')
      ->orWhereJsonContains('properties->action', 'bulk_deactivate')
      ->orWhereJsonContains('properties->action', 'bulk_deleted');
})->count();


    $users = User::select('id','name')
                 ->orderBy('name')
                 ->get();


    $modules = Activity::whereNotNull('properties')
        ->get()
        ->pluck('properties.module')
        ->filter()
        ->unique();


    $actions = Activity::whereNotNull('properties')
        ->get()
        ->pluck('properties.action')
        ->filter()
        ->unique();


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
        'deletedLogs'
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