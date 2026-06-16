<?php

namespace App\Listeners\Task;

use App\Events\Contracts\HasTask;
use Illuminate\Support\Facades\Log;

class LogTaskActivity
{
    public function handle(HasTask $event): void
    {
        $task = $event->getTask();
        $eventName = class_basename($event);
        Log::info("{$eventName}: task #{$task->id} — {$task->title}");
    }
}
