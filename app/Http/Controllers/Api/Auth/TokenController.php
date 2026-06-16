<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class TokenController extends Controller
{
    /**
     * List active tokens.
     *
     * @group Token Management
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()
            ->select(['id', 'name', 'last_used_at', 'created_at'])
            ->latest()
            ->get();

        return response()->json(['data' => $tokens]);
    }

    /**
     * Revoke a token.
     *
     * @group Token Management
     */
    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $token = PersonalAccessToken::find($tokenId);

        if (! $token) {
            return response()->json(['message' => 'Token not found.'], 404);
        }

        if ($token->tokenable_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $token->delete();

        return response()->json(['message' => 'Token revoked successfully.']);
    }
}
