<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blokir akun inactive/expired untuk request API (JSON), tanpa redirect Filament.
 */
class EnsureApiAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (method_exists($user, 'isAccessBlocked') && $user->isAccessBlocked()) {
            return response()->json([
                'message' => 'Akun Anda tidak aktif.',
            ], 403);
        }

        if (method_exists($user, 'isExpired') && $user->isExpired()) {
            return response()->json([
                'message' => 'Akun Anda telah kedaluwarsa.',
            ], 403);
        }

        return $next($request);
    }
}
