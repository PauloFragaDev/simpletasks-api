<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_accessing_nonexistent_task_returns_json_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/tasks/9999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Resource not found.']);
    }

    public function test_accessing_another_users_task_returns_json_403(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task  = Task::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->getJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Forbidden.']);
    }

    public function test_unauthenticated_request_returns_json_401(): void
    {
        $response = $this->getJson('/api/v1/tasks');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_unauthenticated_plain_request_to_api_returns_json_401(): void
    {
        // Plain request WITHOUT Accept: application/json header
        $response = $this->get('/api/v1/tasks');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }
}
