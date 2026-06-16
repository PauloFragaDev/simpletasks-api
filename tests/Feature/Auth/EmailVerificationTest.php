<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    // ── Verification ──────────────────────────────────────────────────────

    public function test_user_can_verify_email_with_valid_signed_url(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->getJson($verificationUrl);

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Email verified successfully.']);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_already_verified_user_gets_success_response(): void
    {
        $user = User::factory()->create(); // factory creates verified users by default

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->getJson($verificationUrl);

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Email already verified.']);
    }

    public function test_email_verification_fails_with_invalid_signature(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)
            ->getJson("/api/auth/verify-email/{$user->id}/invalid-hash");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_verify_email(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->getJson($verificationUrl);

        $response->assertStatus(401);
    }

    // ── Resend ────────────────────────────────────────────────────────────

    public function test_unverified_user_can_resend_verification_email(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->postJson('/api/auth/email/resend');

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Verification link sent.']);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verified_user_gets_already_verified_on_resend(): void
    {
        $user = User::factory()->create(); // verified

        $response = $this->actingAs($user)->postJson('/api/auth/email/resend');

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Email already verified.']);
    }

    public function test_unauthenticated_user_cannot_resend_verification(): void
    {
        $response = $this->postJson('/api/auth/email/resend');

        $response->assertStatus(401);
    }

    // ── Registration sends verification email ─────────────────────────────

    public function test_registration_sends_email_verification_notification(): void
    {
        Notification::fake();

        $this->postJson('/api/register', [
            'name'                  => 'Jane Doe',
            'email'                 => 'jane@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(201);

        $user = \App\Models\User::where('email', 'jane@example.com')->first();
        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
