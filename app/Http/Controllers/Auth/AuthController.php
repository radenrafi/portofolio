<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        /** @var User|null $user */
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if ($user->status !== User::STATUS_ACTIVE) {
            return response()->json([
                'message' => 'Account is inactive.',
            ], 403);
        }

        $deviceName = $credentials['device_name'] ?? $request->userAgent() ?? 'api-token';

        $token = $user->createToken($deviceName, $this->abilitiesFor($user));

        return response()->json([
            'token_type' => 'Bearer',
            'token' => $token->plainTextToken,
            'abilities' => $token->accessToken->abilities,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()?->tokens()->delete();

        return response()->json([
            'message' => 'All sessions have been revoked.',
        ]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validated();

        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('auth.password'),
            ]);
        }

        $user->forceFill([
            'password' => $data['password'],
        ])->save();

        $currentToken = $user->currentAccessToken();

        if ($currentToken) {
            $user->tokens()->where('id', '!=', $currentToken->getKey())->delete();
        } else {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function abilitiesFor(User $user): array
    {
        return match ($user->role) {
            User::ROLE_SUPER_ADMIN => ['super_admin', 'teacher', 'student'],
            User::ROLE_TEACHER => ['teacher', 'student'],
            User::ROLE_STUDENT => ['student'],
            default => ['student'],
        };
    }
}
