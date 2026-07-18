<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\UserSession;
use Jenssegers\Agent\Agent;
use App\Notifications\LoginAlertNotification;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
   public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();

    $request->session()->regenerate();

    $agent = new Agent();

    UserSession::create([
        'user_id'    => auth()->id(),
        'session_id' => session()->getId(),
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'browser'    => $agent->browser(),
        'platform'   => $agent->platform(),
        'login_at'   => now(),
        'is_active'  => true,
    ]);

    auth()->user()->notify(
    new LoginAlertNotification(
        'You have successfully logged in to AgencyOS.'
    )
    );

    return redirect()->intended(route('dashboard', absolute: false));
}

    /**
     * Destroy an authenticated session.
     */
public function destroy(Request $request): RedirectResponse
{
    if ($user = Auth::user()) {

        UserSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->whereNull('logout_at')
            ->latest('id')
            ->first()
            ?->update([
                'logout_at' => now(),
                'is_active' => false,
            ]);
    }

    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
}
}
