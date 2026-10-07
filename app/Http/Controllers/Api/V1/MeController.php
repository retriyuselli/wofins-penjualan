<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PayrollResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\DevicePushToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class MeController extends Controller
{
    /**
     * Return the authenticated user (ESS summary).
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing(['roles']);

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Update profile fields (self only).
     */
    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'date_of_birth' => ['nullable', 'date', 'before:'.now()->subYears(17)->toDateString()],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'department' => ['nullable', 'string', 'in:bisnis,operasional'],
            'emergency_contact' => ['nullable', 'string', 'max:1000'],
        ]);

        if (array_key_exists('email', $data) && $data['email'] !== $user->email && ! $user->hasRole('super_admin')) {
            unset($data['email']);
        }

        $user->fill($data);
        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data' => new UserResource($user->fresh()->loadMissing(['roles'])),
        ]);
    }

    /**
     * Upload / replace avatar.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar_url = $path;
        $user->save();

        return response()->json([
            'message' => 'Avatar berhasil diperbarui.',
            'data' => new UserResource($user->fresh()->loadMissing(['roles'])),
        ]);
    }

    /**
     * Change password; revoke other API tokens.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->password = $request->password;
        $user->save();

        $currentTokenId = $request->user()->currentAccessToken()?->id;
        $user->tokens()
            ->when($currentTokenId, fn ($q) => $q->where('id', '!=', $currentTokenId))
            ->delete();

        return response()->json([
            'message' => 'Password berhasil diubah.',
        ]);
    }

    /**
     * Payroll summary for the authenticated user.
     */
    public function compensation(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $period = (string) $request->query('period', 'year');
        $payroll = $user->payrolls()->latest()->first();

        return response()->json([
            'data' => [
                'period' => $period,
                'current_year' => (int) date('Y'),
                'payroll' => $payroll
                    ? new PayrollResource($payroll)
                    : null,
            ],
        ]);
    }

    /**
     * Daftar sesi/token perangkat (tanpa nilai token).
     */
    public function devices(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentId = $user->currentAccessToken()?->id;

        $devices = $user->tokens()
            ->orderByDesc('last_used_at')
            ->orderByDesc('id')
            ->get()
            ->map(static function ($token) use ($currentId): array {
                return [
                    'id' => (int) $token->id,
                    'name' => $token->name,
                    'last_used_at' => optional($token->last_used_at)?->toIso8601String(),
                    'created_at' => optional($token->created_at)?->toIso8601String(),
                    'is_current' => $currentId !== null && (int) $token->id === (int) $currentId,
                ];
            })
            ->values();

        return response()->json([
            'data' => $devices,
        ]);
    }

    public function storePushToken(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validate([
            'token' => ['required', 'string', 'regex:/^[a-fA-F0-9]{64,200}$/'],
            'environment' => ['required', 'string', 'in:sandbox,production'],
            'purpose' => ['sometimes', 'string', 'in:alert,live_start,live_update'],
            'order_id' => ['nullable', 'integer', 'required_if:purpose,live_update'],
        ]);

        if (! Schema::hasTable('device_push_tokens')) {
            return response()->json(['message' => 'Push belum tersedia.'], 503);
        }

        $attributes = [
            'user_id' => $user->id,
            'environment' => $data['environment'],
        ];
        if (Schema::hasColumn('device_push_tokens', 'purpose')) {
            $attributes['purpose'] = $data['purpose'] ?? 'alert';
            $attributes['order_id'] = ($attributes['purpose'] === 'live_update') ? ($data['order_id'] ?? null) : null;
        }

        DevicePushToken::query()->updateOrCreate(
            ['token' => strtolower($data['token'])],
            $attributes
        );

        return response()->json(['message' => 'Token push tersimpan.']);
    }

    public function destroyPushToken(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validate([
            'token' => ['required', 'string', 'regex:/^[a-fA-F0-9]{64,200}$/'],
        ]);

        if (Schema::hasTable('device_push_tokens')) {
            DevicePushToken::query()
                ->where('user_id', $user->id)
                ->where('token', strtolower($data['token']))
                ->delete();
        }

        return response()->json(['message' => 'Token push dihapus.']);
    }
}
