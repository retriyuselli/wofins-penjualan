<?php

namespace App\Http\Middleware;

use App\Services\AppLicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        $service = app(AppLicenseService::class);

        if (! $service->isEnabled()) {
            return $next($request);
        }

        if ($this->allowsWithoutLicense($request)) {
            return $next($request);
        }

        if ($service->isValid()) {
            return $next($request);
        }

        return redirect()->route('filament.admin.pages.license');
    }

    private function allowsWithoutLicense(Request $request): bool
    {
        if ($request->routeIs([
            'filament.admin.auth.*',
            'filament.admin.pages.license',
        ])) {
            return true;
        }

        if ($request->is('livewire/*')) {
            $raw = (string) $request->getContent();

            return str_contains($raw, 'ActivateLicense')
                || str_contains($raw, 'activate-license');
        }

        return false;
    }
}
