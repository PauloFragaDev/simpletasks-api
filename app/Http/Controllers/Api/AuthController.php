<?php
namespace App\Http\Controllers\Api;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;
    public function __construct(
        private RegisterUserAction $registerUser,
        private LoginUserAction    $loginUser,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user  = $this->registerUser->handle($request->validated());
        $token = $user->createToken($request->header('User-Agent', 'unknown'))->plainTextToken;

        return $this->success(
            data: ['user' => new UserResource($user), 'token' => $token],
            message: 'User registered successfully.',
            status: 201
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->loginUser->handle(
            $request->email,
            $request->password,
            $request->device_name
        );

        return $this->success(
            data: ['user' => new UserResource($result->user), 'token' => $result->token],
            message: 'Login successful.',
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(message: 'Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(data: new UserResource($request->user()));
    }
}
