<?php

namespace App\Support;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Branding per company: logo, favicon, image login, nama.
 * Guest: cookie kunjungan terakhir, lalu fallback aset platform.
 */
class CompanyBrand
{
    public const COOKIE = 'wofins_brand_company';

    public const DEFAULT_LOGO = 'images/logomki.png';

    public const DEFAULT_FAVICON = 'images/favicon_makna.png';

    public const DEFAULT_LOGIN_IMAGE = 'images/login-default.jpg';

    /** @var array<string, mixed>|null */
    protected static ?array $memo = null;

    public static function remember(?User $user = null): void
    {
        $id = UserVisibility::companyId($user ?? Auth::user());

        if (! $id) {
            return;
        }

        static::rememberCompanyId($id);
    }

    public static function rememberCompanyId(int $companyId): void
    {
        if ($companyId < 1) {
            return;
        }

        Cookie::queue(cookie(self::COOKIE, (string) $companyId, 60 * 24 * 180));
        static::$memo = null;
    }

    public static function forgetCache(?int $companyId = null): void
    {
        $suffix = $companyId ?: 'default';
        Cache::forget('wofins.brand.'.$suffix);
        static::$memo = null;
    }

    public static function name(): string
    {
        return (string) static::resolved()['name'];
    }

    public static function model(?User $user = null): ?Company
    {
        $id = static::companyId($user);

        if (! $id) {
            return null;
        }

        try {
            if (! Schema::hasTable('companies')) {
                return null;
            }

            return Company::query()->find($id);
        } catch (Throwable) {
            return null;
        }
    }

    public static function logoDataUri(): string
    {
        $path = static::logoFilePath();
        if (! is_string($path) || ! file_exists($path)) {
            return '';
        }

        $mime = @mime_content_type($path) ?: 'image/png';
        $data = @file_get_contents($path);
        if (! is_string($data) || $data === '') {
            return '';
        }

        return 'data:'.$mime.';base64,'.base64_encode($data);
    }

    public static function version(): int
    {
        $brand = static::resolved();
        $fileVersions = [
            @filemtime((string) $brand['logo_path']),
            @filemtime((string) $brand['favicon_path']),
            @filemtime((string) $brand['login_image_path']),
        ];

        return max((int) $brand['version'], ...array_map('intval', $fileVersions));
    }

    public static function logoUrl(): string
    {
        return (string) static::resolved()['logo_url'];
    }

    public static function faviconUrl(): string
    {
        return (string) static::resolved()['favicon_url'];
    }

    public static function logoFilePath(): string
    {
        return (string) static::resolved()['logo_path'];
    }

    public static function faviconFilePath(): string
    {
        return (string) static::resolved()['favicon_path'];
    }

    public static function loginImageFilePath(): string
    {
        return (string) static::resolved()['login_image_path'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function viewData(): array
    {
        $brand = static::resolved();

        return [
            'companyName' => $brand['name'],
            'companyAddress' => $brand['address'],
            'companyEmail' => $brand['email'],
            'companyPhone' => $brand['phone'],
            'companyWebsite' => $brand['website'] ?? null,
            'companyLogoUrl' => $brand['logo_url'],
            'companyFaviconUrl' => $brand['favicon_url'],
            'companyBrandVersion' => $brand['version'],
        ];
    }

    public static function companyId(?User $user = null): ?int
    {
        $user ??= Auth::user();

        if ($user instanceof User) {
            if (ProFeatures::actorIsSuperAdmin() && ! UserVisibility::companyId($user)) {
                return null;
            }

            $id = UserVisibility::companyId($user);
            if ($id) {
                return $id;
            }
        }

        try {
            $fromCookie = (int) request()->cookie(self::COOKIE, 0);
        } catch (Throwable) {
            $fromCookie = 0;
        }

        return $fromCookie > 0 ? $fromCookie : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function resolved(): array
    {
        if (static::$memo !== null) {
            return static::$memo;
        }

        $companyId = static::companyId();
        $cacheKey = 'wofins.brand.'.($companyId ?: 'default');

        static::$memo = Cache::remember($cacheKey, 3600, function () use ($companyId) {
            $company = null;

            try {
                if ($companyId && Schema::hasTable('companies')) {
                    $company = Company::query()->find($companyId);
                }
            } catch (Throwable) {
                $company = null;
            }

            return [
                'name' => $company?->company_name ?: config('app.name'),
                'address' => $company?->address,
                'email' => $company?->email,
                'phone' => $company?->phone,
                'website' => $company?->website,
                'logo_path' => static::storageFilePath($company?->logo_url, self::DEFAULT_LOGO),
                'favicon_path' => static::storageFilePath($company?->favicon_url, self::DEFAULT_FAVICON),
                'login_image_path' => static::storageFilePath($company?->image_login, self::DEFAULT_LOGIN_IMAGE),
                'logo_url' => static::publicUrl($company?->logo_url, self::DEFAULT_LOGO),
                'favicon_url' => static::publicUrl($company?->favicon_url, self::DEFAULT_FAVICON),
                'version' => $company?->updated_at?->getTimestamp() ?? 1,
            ];
        });

        return static::$memo;
    }

    protected static function storageFilePath(?string $path, string $default): string
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->path($path);
        }

        return public_path($default);
    }

    protected static function publicUrl(?string $path, string $default): string
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/'.ltrim($path, '/'));
        }

        return asset($default);
    }
}
