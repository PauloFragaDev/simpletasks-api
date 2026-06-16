<?php

namespace App\Events;

use App\Events\Contracts\HasTask;
use App\Models\Task;
use Illuminate\Foundation\Events\Dispatchable;

class TaskUpdated implements HasTask
{
    use Dispatchable;

    public function __construct(public readonly Task $task) {}

    public function getTask(): Task
    {
        return $this->task;
    }
}
