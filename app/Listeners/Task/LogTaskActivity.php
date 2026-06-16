<?php

namespace App\Listeners\Task;

use Illuminate\Support\Facades\Log;

class LogTaskActivity
{
    public function handle(object $event): void
    {
        $task = $event->task;
        $eventName = class_basename($event);
        Log::info("{$eventName}: task #{$task->id} — {$task->title}");
    }
}
