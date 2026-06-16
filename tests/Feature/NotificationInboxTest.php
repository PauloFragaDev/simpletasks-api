<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationInboxTest extends TestCase
{
    use RefreshDatabase;

    // ── List ─────────────────────────────────────────────────────────────

    public function test_user_can_list_their_notifications(): void
    {
        $user = User::factory()->create();
        $user->notify(new WelcomeNotification());

        $response = $this->actingAs($user)->getJson('/api/v1/notifications');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'data', 'read_at', 'created_at'],
                ],
            ]);

        $this->assertCount(1, $response->json('data'));
    }

    public function test_user_only_sees_their_own_notifications(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $userB->notify(new WelcomeNotification()); // other user's notification
        $userA->notify(new WelcomeNotification());

        $response = $this->actingAs($userA)->getJson('/api/v1/notifications');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_unauthenticated_user_cannot_list_notifications(): void
    {
        $this->getJson('/api/v1/notifications')->assertStatus(401);
    }

    // ── Mark as read ──────────────────────────────────────────────────────

    public function test_user_can_mark_a_notification_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new WelcomeNotification());
        $notification = $user->notifications()->first();

        $this->assertNull($notification->read_at);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Notification marked as read.']);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $userB->notify(new WelcomeNotification());
        $notification = $userB->notifications()->first();

        $response = $this->actingAs($userA)
            ->postJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertStatus(404);
    }

    public function test_marking_nonexistent_notification_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/notifications/nonexistent-uuid/read');

        $response->assertStatus(404);
    }

    // ── Mark all as read ──────────────────────────────────────────────────

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new WelcomeNotification());
        $user->notify(new WelcomeNotification());

        $response = $this->actingAs($user)
            ->postJson('/api/v1/notifications/read-all');

        $response->assertOk()
            ->assertJsonFragment(['message' => 'All notifications marked as read.']);

        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    public function test_unauthenticated_user_cannot_mark_notifications_as_read(): void
    {
        $this->postJson('/api/v1/notifications/read-all')->assertStatus(401);
    }
}
