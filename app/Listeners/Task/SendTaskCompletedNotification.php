<?php

namespace App\Listeners\Task;

use App\Events\TaskCompleted;
use App\Notifications\TaskCompletedNotification;

class SendTaskCompletedNotification
{
    public function handle(TaskCompleted $event): void
    {
        $event->task->user->notify(new TaskCompletedNotification($event->task));
    }
}
