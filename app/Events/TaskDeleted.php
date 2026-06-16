<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Foundation\Events\Dispatchable;

class TaskDeleted
{
    use Dispatchable;

    public function __construct(public readonly Task $task) {}
}
