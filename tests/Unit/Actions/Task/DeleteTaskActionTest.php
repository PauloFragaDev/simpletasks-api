<?php
namespace Tests\Unit\Actions\Task;

use App\Actions\Task\DeleteTaskAction;
use App\Events\TaskDeleted;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DeleteTaskActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_deletes_the_task(): void
    {
        Event::fake();
        $task   = Task::factory()->create();
        $action = new DeleteTaskAction();

        $action->handle($task);

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_dispatches_task_deleted_event(): void
    {
        Event::fake();
        $task   = Task::factory()->create();
        $action = new DeleteTaskAction();

        $action->handle($task);

        Event::assertDispatched(TaskDeleted::class, fn ($e) => $e->task->id === $task->id);
    }
}
