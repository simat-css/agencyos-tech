<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserActionNotification extends Notification
{

    use Queueable;


    public function __construct(
        public string $message
    )
    {

    }



    public function via($notifiable)
    {
        return [
            'database'
        ];
    }




    public function toDatabase($notifiable)
    {

        return [

            'message'=>$this->message,
            'module'=>'User',
            'created_at'=>now()

        ];

    }

}