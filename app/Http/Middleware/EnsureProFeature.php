<?php

namespace App\Http\Middleware;

use App\Support\CompanySubscription;
use App\Support\PricingPlans;
use App\Support\ProFeatures;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProFeature
{
    public function handle(Request $request, Closure $next, string $feature = PricingPlans::FEATURE_PAYROLL): Response
    {
        if (ProFeatures::allows($feature)) {
            return $next($request);
        }

        $message = CompanySubscription::upgradeMessage($feature);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 403);
        }

        $previous = url()->previous();
        $fallback = route('harga');
        $home = rtrim((string) url('/'), '/');
        $previousNorm = rtrim((string) $previous, '/');

        $previousIsUseful = filled($previous)
            && $previous !== $request->fullUrl()
            && $previousNorm !== $home
            && $previousNorm !== rtrim((string) config('app.url'), '/');

        if (! $previousIsUseful) {
            return redirect()->to($fallback)->with('error', $message);
        }

        return redirect()->to($previous)->with('error', $message);
    }
}
