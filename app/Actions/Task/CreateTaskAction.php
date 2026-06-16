<?php

namespace App\Actions\Task;

use App\Events\TaskCreated;
use App\Models\Task;
use App\Models\User;

class CreateTaskAction
{
    public function handle(User $user, array $data): Task
    {
        $task = $user->tasks()->create($data);
        TaskCreated::dispatch($task);

        return $task;
    }
}
