<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ProductCreatedNotifier
{
    public function notify(Product $product, string $action = 'created'): void
    {
        if (! in_array($action, ['created', 'updated', 'deleted'], true)) {
            return;
        }

        if (! Schema::hasTable('app_notifications')) {
            return;
        }

        if ($action === 'updated') {
            $changes = array_diff(array_keys($product->getChanges()), ['last_edited_by_id', 'updated_at']);
            if ($changes === []) {
                return;
            }
        }

        $actor = $this->actor($product, $action);
        $productName = trim((string) $product->name);
        if ($productName === '') {
            $productName = 'Produk';
        }

        $actorName = trim((string) ($actor?->name ?? ''));
        $actorId = $actor?->id ? (int) $actor->id : null;

        $recipientIds = User::query()
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereNotIn('status', ['inactive', 'terminated']);
            })
            ->pluck('id');

        if ($recipientIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = [];
        foreach ($recipientIds as $userId) {
            $isActor = $actorId !== null && (int) $userId === $actorId;

            $rows[] = [
                'user_id' => (int) $userId,
                'product_id' => $product->id,
                'type' => 'product_'.$action,
                'title' => $this->title($action),
                'body' => mb_substr($this->body($productName, $actorName, $action, $isActor), 0, 500),
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('app_notifications')->insert($rows);
        $imageUrl = $this->imageUrl($product);
        app(PushNotifier::class)->send(array_map(static fn (array $row): array => [
            'user_id' => (int) $row['user_id'],
            'title' => $row['title'],
            'body' => $row['body'],
            'type' => $row['type'],
            'product_id' => $row['product_id'],
            'image_url' => $imageUrl,
        ], $rows));
    }

    private function actor(Product $product, string $action): ?User
    {
        if ($action === 'deleted' && Auth::user() instanceof User) {
            return Auth::user();
        }

        if ($product->last_edited_by_id) {
            $editor = User::query()->find($product->last_edited_by_id);
            if ($editor instanceof User) {
                return $editor;
            }
        }

        return Auth::user() instanceof User ? Auth::user() : null;
    }

    private function title(string $action): string
    {
        return match ($action) {
            'updated' => 'Produk diperbarui',
            'deleted' => 'Produk dihapus',
            default => 'Produk baru',
        };
    }

    private function body(string $productName, string $actorName, string $action, bool $isActor): string
    {
        if ($isActor) {
            return match ($action) {
                'updated' => $productName.' berhasil diperbarui.',
                'deleted' => $productName.' berhasil dihapus.',
                default => $productName.' berhasil dibuat.',
            };
        }

        if ($actorName === '') {
            return match ($action) {
                'updated' => $productName.' sudah diperbarui.',
                'deleted' => $productName.' sudah dihapus.',
                default => $productName.' sudah dibuat.',
            };
        }

        return match ($action) {
            'updated' => $productName.' diperbarui oleh '.$actorName.'.',
            'deleted' => $productName.' dihapus oleh '.$actorName.'.',
            default => $productName.' dibuat oleh '.$actorName.'.',
        };
    }

    private function imageUrl(Product $product): ?string
    {
        $path = trim((string) $product->image);
        if ($path === '') {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $url = Storage::url($path);

        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://') ? $url : url($url);
    }
}
