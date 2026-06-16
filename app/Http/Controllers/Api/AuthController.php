<?php
namespace App\Http\Controllers\Api;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private RegisterUserAction $registerUser,
        private LoginUserAction    $loginUser,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user  = $this->registerUser->handle($request->validated());
        $token = $user->createToken($request->header('User-Agent', 'unknown'))->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => new UserResource($user),
            'token'   => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->loginUser->handle(
            $request->email,
            $request->password,
            $request->header('User-Agent', 'unknown')
        );

        return response()->json([
            'message' => 'Login successful',
            'user'    => new UserResource($result->user),
            'token'   => $result->token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => new UserResource($request->user())]);
    }
}
