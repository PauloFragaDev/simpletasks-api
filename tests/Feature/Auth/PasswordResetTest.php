<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    // ── Forgot Password ───────────────────────────────────────────────────

    public function test_user_can_request_password_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertJsonFragment(['message' => 'If an account with that email exists, a password reset link has been sent.']);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_returns_success_for_unknown_email(): void
    {
        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => 'nobody@example.com',
        ]);

        // Must return 200 — do not reveal if email is registered (security)
        $response->assertOk();
    }

    public function test_forgot_password_requires_valid_email(): void
    {
        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    // ── Reset Password ────────────────────────────────────────────────────

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Password has been reset successfully.']);

        // Verify user can now log in with the new password
        $this->postJson('/api/v1/login', [
            'email'       => $user->email,
            'password'    => 'newpassword123',
            'device_name' => 'Test Device',
        ])->assertOk();
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/reset-password', [
            'token'                 => 'invalid-token',
            'email'                 => $user->email,
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422);
    }

    public function test_reset_password_requires_password_confirmation(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'newpassword123',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    public function test_reset_password_requires_minimum_length(): void
    {
        $user  = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }
}
