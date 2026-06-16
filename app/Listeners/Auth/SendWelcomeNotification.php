<?php

namespace App\Listeners\Auth;

use App\Events\UserRegistered;
use App\Notifications\WelcomeNotification;

class SendWelcomeNotification
{
    public function handle(UserRegistered $event): void
    {
        $event->user->notify(new WelcomeNotification());
    }
}
