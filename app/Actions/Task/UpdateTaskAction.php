<?php

namespace App\Actions\Task;

use App\Enums\TaskStatus;
use App\Events\TaskCompleted;
use App\Events\TaskUpdated;
use App\Models\Task;

class UpdateTaskAction
{
    public function handle(Task $task, array $data): Task
    {
        $becomingDone = isset($data['status'])
            && $task->status !== TaskStatus::Done
            && ($data['status'] === 'done' || $data['status'] === TaskStatus::Done);

        $task->update($data);
        $task->refresh();

        TaskUpdated::dispatch($task);

        if ($becomingDone) {
            TaskCompleted::dispatch($task);
        }

        return $task;
    }
}
