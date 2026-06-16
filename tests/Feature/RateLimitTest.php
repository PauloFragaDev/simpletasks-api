<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login-' . request()->ip());
    }

    public function test_login_is_throttled_after_too_many_attempts(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/login', [
                'email'    => $user->email,
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->postJson('/api/v1/login', [
            'email'    => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
    }

    public function test_register_is_throttled_after_too_many_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/register', [
                'name'                  => 'User',
                'email'                 => "user{$i}@example.com",
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);
        }

        $response = $this->postJson('/api/v1/register', [
            'name'                  => 'User',
            'email'                 => 'user99@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(429);
    }
}
