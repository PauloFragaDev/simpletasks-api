<?php

namespace Tests\Feature\Notifications;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDueReminderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_stored_with_correct_data(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->pending()->create([
            'user_id'  => $user->id,
            'title'    => 'Submit report',
            'due_date' => now()->toDateString(),
        ]);

        $user->notify(new TaskDueReminderNotification($task));

        $notification = $user->notifications()->first();
        $data = $notification->data;
        $this->assertEquals('task_due_reminder', $data['type']);
        $this->assertEquals($task->id, $data['task_id']);
        $this->assertStringContainsString('Submit report', $data['message']);
    }
}
