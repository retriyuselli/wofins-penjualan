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
            'logout',
            'filament.admin.auth.logout',
        ])) {
            return $next($request);
        }

        return redirect()->route('subscription-agreement.gate');
    }
}
