<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Foundation\Events\Dispatchable;

class TaskUpdated
{
    use Dispatchable;

    public function __construct(public readonly Task $task) {}
}
