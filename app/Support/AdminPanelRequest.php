<?php

namespace App\Support;

use Illuminate\Http\Request;

final class AdminPanelRequest
{
    public static function matches(Request $request): bool
    {
        if ($request->is('admin', 'admin/*')) {
            return true;
        }

        return self::isLivewireRequest($request) && self::refererIsAdmin($request);
    }

    public static function isLivewireRequest(Request $request): bool
    {
        if ($request->headers->has('X-Livewire')) {
            return true;
        }

        $path = $request->path();

        return $path === 'livewire'
            || str_starts_with($path, 'livewire/')
            || str_starts_with($path, 'livewire-');
    }

    private static function refererIsAdmin(Request $request): bool
    {
        $referer = (string) $request->headers->get('referer', '');
        if ($referer === '') {
            return false;
        }

        $path = parse_url($referer, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return false;
        }

        return $path === '/admin' || str_starts_with($path, '/admin/');
    }
}
