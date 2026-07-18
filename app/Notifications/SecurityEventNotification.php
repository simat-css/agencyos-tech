<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SecurityEventNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $message
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'      => 'Security Event',
            'message'    => $this->message,
            'module'     => 'Security',
            'type'       => 'security_event',
            'created_at' => now(),
        ];
    }
}