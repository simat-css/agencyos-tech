<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\UserSession;
use Jenssegers\Agent\Agent;

class StoreUserSession
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
{
    $agent = new Agent();

    UserSession::create([
        'user_id'    => $event->user->id,
        'session_id' => session()->getId(),
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'browser'    => $agent->browser(),
        'platform'   => $agent->platform(),
        'login_at'   => now(),
        'is_active'  => true,
    ]);
}
}
