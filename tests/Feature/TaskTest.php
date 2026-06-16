<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    // ── Index ─────────────────────────────────────────────────────────────

    public function test_user_can_list_only_their_own_tasks(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Task::factory()->count(3)->create(['user_id' => $user->id]);
        Task::factory()->count(2)->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_tasks_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(2)->pending()->create(['user_id' => $user->id]);
        Task::factory()->count(3)->done()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?status=pending');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_tasks_can_be_filtered_by_priority(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(2)->highPriority()->create(['user_id' => $user->id]);
        Task::factory()->count(3)->create(['user_id' => $user->id, 'priority' => 'low']);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?priority=high');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_tasks_are_paginated(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(20)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?per_page=5');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 20);
    }

    public function test_per_page_over_100_returns_validation_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?per_page=10000');

        $response->assertStatus(422)
            ->assertJsonValidationErrors('per_page');
    }

    public function test_sort_by_only_accepts_whitelisted_columns(): void
    {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?sort_by=password');

        $response->assertStatus(422)
            ->assertJsonValidationErrors('sort_by');
    }

    public function test_sort_order_only_accepts_asc_or_desc(): void
    {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?sort_order=DROP TABLE');

        $response->assertStatus(422)
            ->assertJsonValidationErrors('sort_order');
    }

    public function test_tasks_can_be_sorted_by_whitelisted_column(): void
    {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'title' => 'Zebra task']);
        Task::factory()->create(['user_id' => $user->id, 'title' => 'Alpha task']);

        $response = $this->actingAs($user)->getJson('/api/v1/tasks?sort_by=title&sort_order=asc');

        $response->assertOk();
        $this->assertEquals('Alpha task', $response->json('data.0.title'));
    }

    public function test_unauthenticated_user_cannot_list_tasks(): void
    {
        $response = $this->getJson('/api/v1/tasks');

        $response->assertStatus(401);
    }

    // ── Store ─────────────────────────────────────────────────────────────

    public function test_user_can_create_a_task(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/tasks', [
            'title' => 'Buy groceries',
            'description' => 'Milk, bread, eggs',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'task' => ['id', 'title', 'description', 'status', 'priority', 'due_date', 'created_at'],
            ]);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Buy groceries',
        ]);
    }

    public function test_create_task_fails_without_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/tasks', [
            'description' => 'Some description',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    public function test_create_task_fails_with_invalid_status(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/tasks', [
            'title' => 'Test task',
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    public function test_create_task_fails_with_past_due_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/tasks', [
            'title' => 'Test task',
            'due_date' => now()->subDays(1)->format('Y-m-d'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('due_date');
    }

    // ── Show ──────────────────────────────────────────────────────────────

    public function test_user_can_view_their_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson("/api/v1/tasks/{$task->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $task->id);
    }

    public function test_user_cannot_view_another_users_task(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->getJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    // ── Update ────────────────────────────────────────────────────────────

    public function test_user_can_update_their_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->pending()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->patchJson("/api/v1/tasks/{$task->id}", [
            'status' => 'done',
        ]);

        $response->assertOk()
            ->assertJsonPath('task.status', 'done');

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'done']);
    }

    public function test_user_cannot_update_another_users_task(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->patchJson("/api/v1/tasks/{$task->id}", [
            'title' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_update_task_title_when_due_date_is_in_the_past(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->withPastDueDate()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->patchJson("/api/v1/tasks/{$task->id}", [
            'title' => 'Updated title',
            'due_date' => $task->due_date->format('Y-m-d'),
        ]);

        $response->assertOk();
    }

    // ── Destroy ───────────────────────────────────────────────────────────

    public function test_user_can_delete_their_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Task deleted successfully']);

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_user_cannot_delete_another_users_task(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}
