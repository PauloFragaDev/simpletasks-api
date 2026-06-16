<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_at_is_set_when_task_is_marked_done(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->pending()->create(['user_id' => $user->id]);

        $this->assertNull($task->completed_at);

        $this->actingAs($user)->patchJson("/api/tasks/{$task->id}", [
            'status' => 'done',
        ]);

        $this->assertNotNull($task->fresh()->completed_at);
    }

    public function test_completed_at_is_cleared_when_task_is_reopened(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->done()->create([
            'user_id'      => $user->id,
            'completed_at' => now(),
        ]);

        $this->actingAs($user)->patchJson("/api/tasks/{$task->id}", [
            'status' => 'pending',
        ]);

        $this->assertNull($task->fresh()->completed_at);
    }

    public function test_completed_at_appears_in_task_resource(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->done()->create([
            'user_id'      => $user->id,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson("/api/tasks/{$task->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['completed_at']]);
    }

    public function test_task_created_without_completed_at_returns_null(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->pending()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson("/api/tasks/{$task->id}");

        $response->assertOk()
            ->assertJsonPath('data.completed_at', null);
    }
}
