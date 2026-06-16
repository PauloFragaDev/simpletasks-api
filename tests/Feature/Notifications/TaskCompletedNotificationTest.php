<?php

namespace Tests\Feature\Notifications;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskCompletedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskCompletedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_sent_when_task_marked_as_done(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $task = Task::factory()->pending()->create(['user_id' => $user->id]);

        $this->actingAs($user)->putJson("/api/v1/tasks/{$task->id}", [
            'status' => 'done',
        ])->assertOk();

        Notification::assertSentTo($user, TaskCompletedNotification::class);
    }

    public function test_notification_not_sent_when_status_unchanged(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $task = Task::factory()->pending()->create(['user_id' => $user->id]);

        // Update title only — no status change
        $this->actingAs($user)->putJson("/api/v1/tasks/{$task->id}", [
            'title' => 'Updated title',
        ])->assertOk();

        Notification::assertNotSentTo($user, TaskCompletedNotification::class);
    }

    public function test_notification_not_sent_when_task_already_done(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $task = Task::factory()->done()->create(['user_id' => $user->id]);

        // Update a done task — no new TaskCompleted event
        $this->actingAs($user)->putJson("/api/v1/tasks/{$task->id}", [
            'title' => 'New title',
        ])->assertOk();

        Notification::assertNotSentTo($user, TaskCompletedNotification::class);
    }

    public function test_notification_is_stored_in_database_with_correct_data(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->pending()->create([
            'user_id' => $user->id,
            'title'   => 'Buy groceries',
        ]);

        $user->notify(new TaskCompletedNotification($task));

        $this->assertDatabaseHas('notifications', [
            'notifiable_id'   => $user->id,
            'notifiable_type' => User::class,
        ]);

        $notification = $user->notifications()->first();
        $data         = $notification->data;
        $this->assertEquals('task_completed', $data['type']);
        $this->assertEquals($task->id, $data['task_id']);
        $this->assertStringContainsString('Buy groceries', $data['message']);
    }
}
