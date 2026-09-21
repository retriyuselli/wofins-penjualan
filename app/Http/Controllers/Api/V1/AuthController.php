<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\AppLicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Issue a Sanctum personal access token for mobile / API clients.
     */
    public function login(Request $request, AppLicenseService $license): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        /** @var User|null $user */
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak valid.'],
            ]);
        }

        $this->assertUserCanLogin($user);
        $this->assertLicenseAllowsLogin($license);

        return $this->tokenResponse($user, $credentials['device_name'] ?? 'ios-app');
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    private function assertUserCanLogin(User $user): void
    {
        if ($user->isAccessBlocked()) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda tidak aktif.'],
            ]);
        }

        if ($user->isExpired()) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda telah kedaluwarsa.'],
            ]);
        }
    }

    private function assertLicenseAllowsLogin(AppLicenseService $license): void
    {
        if (! $license->isEnabled()) {
            return;
        }

        $status = $license->status(reverify: false);

        if ($status['valid'] === true) {
            return;
        }

        throw ValidationException::withMessages([
            'email' => [$status['message'] ?: 'Lisensi aplikasi tidak aktif. Hubungi admin untuk perpanjang.'],
        ]);
    }

    private function tokenResponse(User $user, string $deviceName): JsonResponse
    {
        $expiresAt = now()->addDays(max(1, (int) config('sanctum.mobile_token_expiration_days', 30)));
        $token = $user->createToken($deviceName, ['mobile'], $expiresAt)->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
            'user' => new UserResource($user->loadMissing(['roles'])),
        ]);
    }
}
