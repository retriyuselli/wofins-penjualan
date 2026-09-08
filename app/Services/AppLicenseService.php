<?php

namespace App\Services;

use App\Models\AppLicense;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AppLicenseService
{
    public function isEnabled(): bool
    {
        return (bool) config('wofins.license.enabled');
    }

    public function current(): ?AppLicense
    {
        if (! Schema::hasTable('app_licenses')) {
            return null;
        }

        return AppLicense::query()->latest('id')->first();
    }

    /**
     * @return array{valid: bool, status: string, message: string, license: ?AppLicense, days_remaining: ?int}
     */
    public function status(bool $reverify = true, bool $force = false): array
    {
        if (! $this->isEnabled()) {
            return [
                'valid' => true,
                'status' => 'disabled',
                'message' => 'Pemeriksaan lisensi nonaktif di lingkungan ini.',
                'license' => null,
                'days_remaining' => null,
            ];
        }

        $license = $this->current();

        if (! $license) {
            return [
                'valid' => false,
                'status' => 'missing',
                'message' => 'Aplikasi belum diaktivasi. Tempelkan Item Purchase Code dari maknafinance.id.',
                'license' => null,
                'days_remaining' => null,
            ];
        }

        if ($reverify) {
            $license = $this->reverifyIfStale($license, $force) ?? $license;
        }

        if (in_array($license->status, ['expired', 'revoked', 'invalid', 'domain_mismatch'], true)) {
            return [
                'valid' => false,
                'status' => $license->status,
                'message' => $license->last_message ?: 'Lisensi tidak valid.',
                'license' => $license,
                'days_remaining' => $license->daysRemaining(),
            ];
        }

        if (! $license->isWithinPeriod()) {
            return [
                'valid' => false,
                'status' => 'expired',
                'message' => 'Masa berlangganan telah berakhir pada '.$license->ends_at->format('d M Y').'.',
                'license' => $license,
                'days_remaining' => $license->daysRemaining(),
            ];
        }

        return [
            'valid' => true,
            'status' => $license->status ?: 'active',
            'message' => $license->last_message ?: 'Lisensi aktif.',
            'license' => $license,
            'days_remaining' => $license->daysRemaining(),
        ];
    }

    public function isValid(): bool
    {
        return $this->status()['valid'] === true;
    }

    /**
     * @return array{valid: bool, status: string, message: string, license: ?AppLicense, days_remaining: ?int}
     */
    public function activate(string $code, ?string $domain = null): array
    {
        try {
            $payload = $this->requestVerification($code, $domain, true);
        } catch (Throwable $e) {
            Log::warning('Gagal menghubungi server lisensi WOFINS.', ['error' => $e->getMessage()]);

            return [
                'valid' => false,
                'status' => 'unreachable',
                'message' => 'Server lisensi tidak dapat dihubungi. Coba lagi beberapa saat.',
                'license' => $this->current(),
                'days_remaining' => $this->current()?->daysRemaining(),
            ];
        }

        if (! ($payload['valid'] ?? false)) {
            return [
                'valid' => false,
                'status' => (string) ($payload['status'] ?? 'invalid'),
                'message' => (string) ($payload['message'] ?? 'Item Purchase Code tidak valid.'),
                'license' => $this->current(),
                'days_remaining' => $this->current()?->daysRemaining(),
            ];
        }

        try {
            $license = $this->storeFromPayload($code, $payload, $domain);
        } catch (Throwable $e) {
            Log::error('Gagal menyimpan lisensi WOFINS.', ['error' => $e->getMessage()]);

            return [
                'valid' => false,
                'status' => 'error',
                'message' => 'Aktivasi berhasil di server, tetapi gagal disimpan di aplikasi ini.',
                'license' => $this->current(),
                'days_remaining' => $this->current()?->daysRemaining(),
            ];
        }

        return [
            'valid' => true,
            'status' => (string) ($payload['status'] ?? 'active'),
            'message' => (string) ($payload['message'] ?? 'Aplikasi berhasil diaktivasi.'),
            'license' => $license,
            'days_remaining' => $license->daysRemaining(),
        ];
    }

    private function reverifyIfStale(AppLicense $license, bool $force = false): ?AppLicense
    {
        if (! $force) {
            $hours = max(1, (int) config('wofins.license.reverify_hours', 6));
            $stale = ! $license->last_verified_at || $license->last_verified_at->lt(now()->subHours($hours));

            if (! $stale) {
                return $license;
            }
        }

        try {
            $payload = $this->requestVerification($license->code, $license->domain, false);
        } catch (Throwable $e) {
            Log::warning('Gagal verifikasi ulang lisensi WOFINS.', ['error' => $e->getMessage()]);

            return $license;
        }

        if ($payload['valid'] ?? false) {
            return $this->storeFromPayload($license->code, $payload, $license->domain);
        }

        $status = (string) ($payload['status'] ?? 'invalid');
        if (in_array($status, ['expired', 'revoked', 'invalid', 'domain_mismatch'], true)) {
            $license->forceFill([
                'status' => $status,
                'last_verified_at' => now(),
                'last_message' => $payload['message'] ?? $license->last_message,
                'starts_at' => $payload['starts_at'] ?? $license->starts_at,
                'ends_at' => $payload['ends_at'] ?? $license->ends_at,
            ])->save();

            return $license->refresh();
        }

        return $license;
    }

    /**
     * @return array<string, mixed>
     */
    private function requestVerification(string $code, ?string $domain, bool $bind): array
    {
        $server = (string) config('wofins.license.server');
        $path = (string) config('wofins.license.verify_path');
        $url = $server.$path;

        $response = Http::timeout(15)
            ->acceptJson()
            ->asJson()
            ->post($url, [
                'code' => trim($code),
                'domain' => $domain,
                'bind' => $bind,
            ]);

        $json = $response->json();

        if (! is_array($json)) {
            throw new \RuntimeException('Server lisensi tidak merespons JSON yang valid.');
        }

        return $json;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function storeFromPayload(string $code, array $payload, ?string $domain): AppLicense
    {
        $license = $this->current() ?? new AppLicense;

        $license->forceFill([
            'code' => $payload['code'] ?? $code,
            'company_name' => $payload['company_name'] ?? $license->company_name,
            'package' => $payload['package'] ?? $license->package,
            'domain' => $payload['domain'] ?? $domain,
            'starts_at' => $payload['starts_at'],
            'ends_at' => $payload['ends_at'],
            'status' => $payload['status'] ?? 'active',
            'activated_at' => $license->activated_at ?? now(),
            'last_verified_at' => now(),
            'last_message' => $payload['message'] ?? null,
        ])->save();

        return $license->refresh();
    }
}
