<?php
namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Events\UserRegistered;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_user_with_hashed_password(): void
    {
        Event::fake();
        $action = new RegisterUserAction();

        $user = $action->handle([
            'name'     => 'John Doe',
            'email'    => 'john@example.com',
            'password' => 'secret123',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_dispatches_user_registered_event(): void
    {
        Event::fake();
        $action = new RegisterUserAction();

        $user = $action->handle([
            'name'     => 'John Doe',
            'email'    => 'john@example.com',
            'password' => 'secret123',
        ]);

        Event::assertDispatched(UserRegistered::class, fn ($e) => $e->user->id === $user->id);
    }
}
