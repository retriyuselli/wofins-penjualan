<?php

namespace App\Http\Middleware;

use App\Services\AppLicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cek lisensi Item Purchase Code untuk API mobile (JSON 403, bukan redirect admin).
 */
class EnsureApiAppLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        $service = app(AppLicenseService::class);

        if (! $service->isEnabled()) {
            return $next($request);
        }

        $status = $service->status(reverify: false);

        if ($status['valid'] === true) {
            return $next($request);
        }

        return response()->json([
            'message' => $status['message'] ?: 'Lisensi aplikasi tidak aktif.',
            'status' => $status['status'] ?? 'invalid',
        ], 403);
    }
}
