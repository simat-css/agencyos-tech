<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class CompanyActionNotification extends Notification
{
    use Queueable;

    protected string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'type' => 'company'
        ];
    }
}