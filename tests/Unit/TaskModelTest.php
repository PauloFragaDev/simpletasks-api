<?php

namespace Tests\Unit;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_status_is_cast_to_enum(): void
    {
        $task = Task::factory()->pending()->create();

        $this->assertInstanceOf(TaskStatus::class, $task->status);
        $this->assertSame(TaskStatus::Pending, $task->status);
    }

    public function test_task_priority_is_cast_to_enum(): void
    {
        $task = Task::factory()->highPriority()->create();

        $this->assertInstanceOf(TaskPriority::class, $task->priority);
        $this->assertSame(TaskPriority::High, $task->priority);
    }

    public function test_task_status_serializes_as_string_in_json(): void
    {
        $task = Task::factory()->done()->create();

        $array = $task->toArray();

        $this->assertSame('done', $array['status']);
    }
}
