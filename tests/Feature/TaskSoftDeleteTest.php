<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleted_task_is_not_returned_in_listing(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->deleteJson("/api/v1/tasks/{$task->id}");

        $response = $this->actingAs($user)->getJson('/api/v1/tasks');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_deleted_task_remains_in_database(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->deleteJson("/api/v1/tasks/{$task->id}");

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_deleted_task_returns_404_on_show(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->deleteJson("/api/v1/tasks/{$task->id}");

        $response = $this->actingAs($user)->getJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(404);
    }
}
