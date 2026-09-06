<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class AppVersion
{
    private static ?string $memo = null;

    public static function current(): string
    {
        if (self::$memo !== null) {
            return self::$memo;
        }

        $path = storage_path('app/app_version.json');
        $state = self::readState($path);
        $head = self::gitHead();
        $version = $state['version'] ?? config('app.version', '2.0');

        if ($head && ($state['head'] ?? null) !== $head) {
            if (! empty($state['head'])) {
                $version = self::bumpMinor($version);
            }

            self::writeState($path, [
                'version' => $version,
                'head' => $head,
            ]);
        } elseif (! is_file($path)) {
            self::writeState($path, [
                'version' => $version,
                'head' => $head,
            ]);
        }

        return self::$memo = $version;
    }

    /**
     * @return array{version?: string, head?: string|null}
     */
    private static function readState(string $path): array
    {
        if (! is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array{version: string, head: string|null}  $state
     */
    private static function writeState(string $path, array $state): void
    {
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private static function bumpMinor(string $version): string
    {
        $parts = explode('.', $version, 2);
        $major = is_numeric($parts[0] ?? null) ? (int) $parts[0] : 2;
        $minor = is_numeric($parts[1] ?? null) ? (int) $parts[1] : 0;

        return $major.'.'.($minor + 1);
    }

    private static function gitHead(): ?string
    {
        if (! file_exists(base_path('.git'))) {
            return null;
        }

        try {
            $result = Process::path(base_path())
                ->timeout(3)
                ->run(['git', 'rev-parse', 'HEAD']);

            if (! $result->successful()) {
                return null;
            }

            $head = trim($result->output());

            return $head !== '' ? $head : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
