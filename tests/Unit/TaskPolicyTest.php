<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    private TaskPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TaskPolicy();
    }

    public function test_user_can_view_their_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->view($user, $task));
    }

    public function test_user_cannot_view_another_users_task(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task  = Task::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->view($other, $task));
    }

    public function test_user_can_update_their_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->update($user, $task));
    }

    public function test_user_cannot_update_another_users_task(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task  = Task::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->update($other, $task));
    }

    public function test_user_can_delete_their_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->delete($user, $task));
    }

    public function test_user_cannot_delete_another_users_task(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task  = Task::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->delete($other, $task));
    }
}
