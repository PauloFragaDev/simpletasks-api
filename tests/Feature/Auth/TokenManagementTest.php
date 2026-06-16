<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenManagementTest extends TestCase
{
    use RefreshDatabase;

    // ── List tokens ───────────────────────────────────────────────────────

    public function test_user_can_list_their_tokens(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('My Phone')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/auth/tokens');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'last_used_at', 'created_at'],
                ],
            ]);

        $this->assertCount(1, $response->json('data'));
    }

    public function test_user_only_sees_their_own_tokens(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $userB->createToken('Other Token');
        $tokenA = $userA->createToken('My Token')->plainTextToken;

        $response = $this->withToken($tokenA)->getJson('/api/v1/auth/tokens');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_unauthenticated_user_cannot_list_tokens(): void
    {
        $this->getJson('/api/v1/auth/tokens')->assertStatus(401);
    }

    // ── Revoke token ──────────────────────────────────────────────────────

    public function test_user_can_revoke_a_specific_token(): void
    {
        $user    = User::factory()->create();
        $tokenA  = $user->createToken('Phone')->plainTextToken;
        $tokenB  = $user->createToken('Laptop');

        $response = $this->withToken($tokenA)
            ->deleteJson("/api/v1/auth/tokens/{$tokenB->accessToken->id}");

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Token revoked successfully.']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenB->accessToken->id,
        ]);
    }

    public function test_user_can_revoke_their_current_token(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('My Device');

        $response = $this->withToken($token->plainTextToken)
            ->deleteJson("/api/v1/auth/tokens/{$token->accessToken->id}");

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Token revoked successfully.']);
    }

    public function test_user_cannot_revoke_another_users_token(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $tokenA     = $userA->createToken('My Device')->plainTextToken;
        $tokenB     = $userB->createToken('Other Device');

        $response = $this->withToken($tokenA)
            ->deleteJson("/api/v1/auth/tokens/{$tokenB->accessToken->id}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_revoke_tokens(): void
    {
        $this->deleteJson('/api/v1/auth/tokens/1')->assertStatus(401);
    }

    public function test_revoking_nonexistent_token_returns_404(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('My Device')->plainTextToken;

        $response = $this->withToken($token)->deleteJson('/api/v1/auth/tokens/9999');

        $response->assertStatus(404);
    }
}
