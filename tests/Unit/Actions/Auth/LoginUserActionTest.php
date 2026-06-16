<?php
namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoginUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_token_for_valid_credentials(): void
    {
        $user   = User::factory()->create(['password' => bcrypt('secret123')]);
        $action = new LoginUserAction();

        $token = $action->handle($user->email, 'secret123', 'Test Device');

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function test_throws_validation_exception_for_wrong_password(): void
    {
        $user   = User::factory()->create(['password' => bcrypt('correct')]);
        $action = new LoginUserAction();

        $this->expectException(ValidationException::class);

        $action->handle($user->email, 'wrong', 'Test Device');
    }

    public function test_throws_validation_exception_for_nonexistent_email(): void
    {
        $action = new LoginUserAction();

        $this->expectException(ValidationException::class);

        $action->handle('nobody@example.com', 'password', 'Test Device');
    }
}
