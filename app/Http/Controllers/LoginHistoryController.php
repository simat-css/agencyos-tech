<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;

class LoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        $authUser = auth()->user();

        $query = UserSession::with([
            'user.company',
            'user.department',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Company Restriction
        |--------------------------------------------------------------------------
        */

        if (!$authUser->hasRole('Super Admin')) {

            $companyUserIds = User::where(
                'company_id',
                $authUser->company_id
            )->pluck('id');

            $query->whereIn('user_id', $companyUserIds);
        }

       /*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

if ($request->filled('search')) {

    $search = $request->search;

    $query->where(function ($q) use ($search) {

        $q->where('ip_address', 'like', "%{$search}%")
            ->orWhere('browser', 'like', "%{$search}%")
            ->orWhere('platform', 'like', "%{$search}%")
            ->orWhereHas('user', function ($userQuery) use ($search) {

                $userQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
    });
}

/*
|--------------------------------------------------------------------------
| Status Filter
|--------------------------------------------------------------------------
*/

if ($request->filled('status')) {

    $query->where(
        'is_active',
        $request->status
    );
}

/*
|--------------------------------------------------------------------------
| Date Filter
|--------------------------------------------------------------------------
*/

if ($request->filled('date')) {

    $query->whereDate(
        'login_at',
        $request->date
    );
}

      /*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$statsQuery = UserSession::query();

if (!$authUser->hasRole('Super Admin')) {

    $statsQuery->whereIn(
        'user_id',
        $companyUserIds
    );
}

$stats = [

    'total_sessions' => (clone $statsQuery)->count(),

    'active_sessions' => (clone $statsQuery)
        ->where('is_active', 1)
        ->count(),

    'today_sessions' => (clone $statsQuery)
        ->whereDate('login_at', today())
        ->count(),

    'unique_users' => (clone $statsQuery)
        ->distinct('user_id')
        ->count('user_id'),

];
        $sessions = $query
            ->latest('login_at')
            ->paginate(20)
            ->withQueryString();

        return view(
            'login-history.index',
            compact(
                'sessions',
                'stats'
            )
        );
    }

   public function destroy(UserSession $session)
{
    $session->delete();

    return redirect()
        ->route('login-history.index')
        ->with(
            'success',
            'Login history deleted successfully.'
        );
}
}