<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SensitiveFileController extends Controller
{
    public function order(Request $request, Order $order, string $field): Response
    {
        abort_unless(in_array($field, ['doc_kontrak', 'agreement_product'], true), 404);
        Gate::authorize('view', $order);

        return $this->stream($order->{$field}, download: $request->boolean('download'));
    }

    private function stream(mixed $value, ?string $name = null, bool $download = false): Response
    {
        $path = $this->path($value);
        abort_if($path === null, 404);

        $headers = ['Cache-Control' => 'private, no-store, max-age=0'];
        $filename = $name ?: basename($path);

        foreach (['private', 'public'] as $disk) {
            if (! Storage::disk($disk)->exists($path)) {
                continue;
            }

            if ($disk === 'public' && ! Storage::disk('private')->exists($path)) {
                $stream = Storage::disk('public')->readStream($path);
                if (is_resource($stream)) {
                    Storage::disk('private')->writeStream($path, $stream);
                    fclose($stream);
                }
            }

            return $download
                ? Storage::disk($disk)->download($path, $filename, $headers)
                : Storage::disk($disk)->response($path, $filename, $headers);
        }

        abort(404);
    }

    private function path(mixed $value): ?string
    {
        if (is_string($value)) {
            $trimmed = trim($value);
            if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
                $decoded = json_decode($trimmed, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
            }
        }

        if (is_array($value)) {
            $value = collect($value)->filter(fn ($item) => is_string($item) && $item !== '')->last();
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $path = ltrim(str_replace('\\', '/', trim($value)), '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return ($path === '' || str_contains($path, '..')) ? null : $path;
    }
}
