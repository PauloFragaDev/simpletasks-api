<?php

namespace Tests\Unit\Actions\Task;

use App\Actions\Task\CreateTaskAction;
use App\Events\TaskCreated;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CreateTaskActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_task_for_user(): void
    {
        Event::fake();
        $user   = User::factory()->create();
        $action = new CreateTaskAction();

        $task = $action->handle($user, ['title' => 'Test task', 'status' => 'pending', 'priority' => 'medium']);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertDatabaseHas('tasks', ['title' => 'Test task', 'user_id' => $user->id]);
    }

    public function test_dispatches_task_created_event(): void
    {
        Event::fake();
        $user   = User::factory()->create();
        $action = new CreateTaskAction();

        $task = $action->handle($user, ['title' => 'Test task', 'status' => 'pending', 'priority' => 'medium']);

        Event::assertDispatched(TaskCreated::class, fn ($e) => $e->task->id === $task->id);
    }
}
