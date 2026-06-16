<?php

namespace Tests\Unit\Actions\Task;

use App\Actions\Task\UpdateTaskAction;
use App\Events\TaskCompleted;
use App\Events\TaskUpdated;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UpdateTaskActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_task_data(): void
    {
        Event::fake();
        $task   = Task::factory()->pending()->create();
        $action = new UpdateTaskAction();

        $updated = $action->handle($task, ['title' => 'Updated title']);

        $this->assertSame('Updated title', $updated->title);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Updated title']);
    }

    public function test_dispatches_task_updated_event(): void
    {
        Event::fake();
        $task   = Task::factory()->pending()->create();
        $action = new UpdateTaskAction();

        $action->handle($task, ['title' => 'Updated']);

        Event::assertDispatched(TaskUpdated::class, fn ($e) => $e->task->id === $task->id);
    }

    public function test_dispatches_task_completed_event_when_status_changes_to_done(): void
    {
        Event::fake();
        $task   = Task::factory()->pending()->create();
        $action = new UpdateTaskAction();

        $action->handle($task, ['status' => 'done']);

        Event::assertDispatched(TaskCompleted::class, fn ($e) => $e->task->id === $task->id);
    }

    public function test_does_not_dispatch_task_completed_when_status_stays_the_same(): void
    {
        Event::fake();
        $task   = Task::factory()->done()->create();
        $action = new UpdateTaskAction();

        $action->handle($task, ['title' => 'New title']);

        Event::assertNotDispatched(TaskCompleted::class);
    }
}
