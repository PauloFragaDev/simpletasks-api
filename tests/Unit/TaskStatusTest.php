<?php

namespace Tests\Unit;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskStatusTest extends TestCase
{
    public function test_task_status_has_expected_values(): void
    {
        $this->assertSame('pending', TaskStatus::Pending->value);
        $this->assertSame('in_progress', TaskStatus::InProgress->value);
        $this->assertSame('done', TaskStatus::Done->value);
    }

    public function test_task_priority_has_expected_values(): void
    {
        $this->assertSame('low', TaskPriority::Low->value);
        $this->assertSame('medium', TaskPriority::Medium->value);
        $this->assertSame('high', TaskPriority::High->value);
    }

    public function test_task_status_can_be_created_from_string(): void
    {
        $this->assertSame(TaskStatus::Pending, TaskStatus::from('pending'));
        $this->assertSame(TaskStatus::InProgress, TaskStatus::from('in_progress'));
        $this->assertSame(TaskStatus::Done, TaskStatus::from('done'));
    }

    public function test_task_priority_can_be_created_from_string(): void
    {
        $this->assertSame(TaskPriority::Low, TaskPriority::from('low'));
        $this->assertSame(TaskPriority::Medium, TaskPriority::from('medium'));
        $this->assertSame(TaskPriority::High, TaskPriority::from('high'));
    }
}
