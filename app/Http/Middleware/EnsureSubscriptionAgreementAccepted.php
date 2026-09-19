<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionAgreementAcceptanceService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionAgreementAccepted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $service = app(SubscriptionAgreementAcceptanceService::class);

        if (! $service->needsAcceptance($user)) {
            return $next($request);
        }

        if ($request->routeIs([
            'subscription-agreement.gate',
            'subscription-agreement.accept',
            'companies.subscription-agreement',
            'filament.admin.pages.license',
            'logout',
            'filament.admin.auth.logout',
        ])) {
            return $next($request);
        }

        if ($request->is('livewire/*')) {
            $raw = (string) $request->getContent();
            if ($raw !== '' && preg_match('/"name"\s*:\s*"[^"]*activate-license[^"]*"/i', $raw)) {
                return $next($request);
            }
        }

        return redirect()->route('subscription-agreement.gate');
    }
}
