<?php
namespace App\Actions\Task;

use App\Events\TaskDeleted;
use App\Models\Task;

class DeleteTaskAction
{
    public function handle(Task $task): void
    {
        $task->delete();
        TaskDeleted::dispatch($task);
    }
}
