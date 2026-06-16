<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDueReminderNotification;
use Illuminate\Console\Command;

class SendTaskDueReminders extends Command
{
    protected $signature   = 'tasks:send-due-reminders';
    protected $description = 'Send reminders for tasks due today';

    public function handle(): int
    {
        $tasks = Task::with('user')
            ->whereDate('due_date', today())
            ->where('status', '!=', 'done')
            ->get();

        foreach ($tasks as $task) {
            $task->user->notify(new TaskDueReminderNotification($task));
        }

        $this->info("Sent {$tasks->count()} due reminder(s).");

        return self::SUCCESS;
    }
}
