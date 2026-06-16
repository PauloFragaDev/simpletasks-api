<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WelcomeNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_notification_is_sent_on_registration(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/register', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(201);

        $user = User::where('email', 'john@example.com')->first();

        Notification::assertSentTo($user, WelcomeNotification::class);
    }

    public function test_welcome_notification_is_stored_in_database(): void
    {
        $user = User::factory()->create();

        $user->notify(new WelcomeNotification());

        $this->assertDatabaseHas('notifications', [
            'notifiable_id'   => $user->id,
            'notifiable_type' => User::class,
        ]);

        $notification = $user->notifications()->first();
        $data = $notification->data;
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('type', $data);
        $this->assertEquals('welcome', $data['type']);
    }
}
