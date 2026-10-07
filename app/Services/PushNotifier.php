<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\DevicePushToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PushNotifier
{
    public function __construct(private readonly ApnsClient $apns) {}

    /**
     * @param  list<array<string, mixed>>  $messages
     */
    public function send(array $messages): void
    {
        if ($messages === [] || ! $this->apns->enabled() || ! Schema::hasTable('device_push_tokens')) {
            return;
        }

        try {
            foreach ($messages as $message) {
                $userId = (int) $message['user_id'];
                $tokens = $this->alertTokens($userId);
                if ($tokens->isEmpty()) {
                    continue;
                }
                $badge = $this->badge($userId);
                $type = (string) ($message['type'] ?? '');
                $context = [
                    'category' => str_ends_with($type, '_deleted') ? 'WO_READ' : 'WO_OPEN',
                    'notification_id' => $this->notificationId($message, $userId),
                    'type' => $type,
                    'product_id' => $message['product_id'] ?? null,
                    'order_id' => $message['order_id'] ?? null,
                    'image_url' => $message['image_url'] ?? null,
                ];
                foreach ($tokens as $token) {
                    $this->apns->send($token, (string) $message['title'], (string) $message['body'], $badge, $context);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('push_notifier_failed', ['message' => $e->getMessage()]);
        }
    }

    public function syncBadge(int $userId): void
    {
        if (! $this->apns->enabled() || ! Schema::hasTable('device_push_tokens')) {
            return;
        }

        try {
            $badge = $this->badge($userId);
            foreach ($this->alertTokens($userId) as $token) {
                $this->apns->sendSilent($token, $badge);
            }
        } catch (\Throwable $e) {
            Log::warning('push_notifier_failed', ['message' => $e->getMessage()]);
        }
    }

    /**
     * @param  list<int>  $userIds
     */
    public function syncProjectActivity(array $userIds, int $orderId, string $projectName, string $event, string $status, string $detail): void
    {
        if (! $this->apns->enabled() || ! $this->hasPurposeColumn() || $userIds === []) {
            return;
        }

        $purpose = $event === 'start' ? 'live_start' : 'live_update';

        try {
            $tokens = DevicePushToken::query()
                ->whereIn('user_id', $userIds)
                ->where('purpose', $purpose)
                ->when($purpose === 'live_update', fn ($query) => $query->where('order_id', $orderId))
                ->get();

            foreach ($tokens as $token) {
                $this->apns->sendLiveActivity($token, $event, [
                    'projectName' => $projectName,
                    'orderId' => $orderId,
                    'status' => $status,
                    'detail' => $detail,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('push_notifier_failed', ['message' => $e->getMessage()]);
        }
    }

    private function badge(int $userId): int
    {
        if (! Schema::hasTable('app_notifications')) {
            return 0;
        }

        return AppNotification::query()->where('user_id', $userId)->whereNull('read_at')->count();
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function notificationId(array $message, int $userId): ?int
    {
        if (! empty($message['notification_id'])) {
            return (int) $message['notification_id'];
        }
        if (! Schema::hasTable('app_notifications')) {
            return null;
        }

        $query = AppNotification::query()->where('user_id', $userId);
        if (! empty($message['type'])) {
            $query->where('type', $message['type']);
        }
        if (! empty($message['product_id'])) {
            $query->where('product_id', $message['product_id']);
        }
        if (! empty($message['order_id'])) {
            $query->where('order_id', $message['order_id']);
        }

        $id = $query->latest('id')->value('id');

        return $id === null ? null : (int) $id;
    }

    private function alertTokens(int $userId)
    {
        $query = DevicePushToken::query()->where('user_id', $userId);
        if ($this->hasPurposeColumn()) {
            $query->where(function ($inner) {
                $inner->where('purpose', 'alert')->orWhereNull('purpose');
            });
        }

        return $query->get();
    }

    private function hasPurposeColumn(): bool
    {
        return Schema::hasTable('device_push_tokens') && Schema::hasColumn('device_push_tokens', 'purpose');
    }
}
