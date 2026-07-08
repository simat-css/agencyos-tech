<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\UserSession;

class UpdateUserLogout
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
    public function handle(Logout $event): void
{
    UserSession::where('user_id', $event->user->id)
        ->where('session_id', session()->getId())
        ->where('is_active', true)
        ->update([
            'logout_at' => now(),
            'is_active' => false,
        ]);
}
}
