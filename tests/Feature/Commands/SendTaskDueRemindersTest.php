<?php

namespace Tests\Feature\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendTaskDueRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_notification_for_tasks_due_today(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Task::factory()->pending()->create([
            'user_id'  => $user->id,
            'due_date' => now()->toDateString(),
        ]);

        $this->artisan('tasks:send-due-reminders')->assertExitCode(0);

        Notification::assertSentTo($user, TaskDueReminderNotification::class);
    }

    public function test_command_skips_done_tasks(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Task::factory()->done()->create([
            'user_id'  => $user->id,
            'due_date' => now()->toDateString(),
        ]);

        $this->artisan('tasks:send-due-reminders')->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_command_skips_tasks_not_due_today(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Task::factory()->pending()->create([
            'user_id'  => $user->id,
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->artisan('tasks:send-due-reminders')->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_command_sends_to_multiple_users(): void
    {
        Notification::fake();

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Task::factory()->pending()->create(['user_id' => $userA->id, 'due_date' => now()->toDateString()]);
        Task::factory()->pending()->create(['user_id' => $userB->id, 'due_date' => now()->toDateString()]);

        $this->artisan('tasks:send-due-reminders')->assertExitCode(0);

        Notification::assertSentTo($userA, TaskDueReminderNotification::class);
        Notification::assertSentTo($userB, TaskDueReminderNotification::class);
    }
}
