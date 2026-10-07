<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! Schema::hasTable('app_notifications')) {
            return response()->json([
                'data' => [],
                'unread_count' => 0,
            ]);
        }

        $base = AppNotification::query()->where('user_id', $user->id);
        $unread = (clone $base)->whereNull('read_at')->count();

        if ($request->boolean('count')) {
            return response()->json([
                'data' => [],
                'unread_count' => $unread,
            ]);
        }

        $items = (clone $base)
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(fn (AppNotification $notification) => $this->payload($notification))
            ->values();

        return response()->json([
            'data' => $items,
            'unread_count' => $unread,
        ]);
    }

    public function read(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $notification = AppNotification::query()
            ->where('user_id', $user->id)
            ->whereKey($id)
            ->first();

        if (! $notification) {
            return response()->json([
                'message' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        if ($notification->read_at === null) {
            $notification->read_at = now();
            $notification->save();
            app(\App\Services\PushNotifier::class)->syncBadge((int) $user->id);
        }

        return response()->json([
            'data' => $this->payload($notification),
            'unread_count' => AppNotification::query()
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->count(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(AppNotification $notification): array
    {
        return [
            'id' => (int) $notification->id,
            'title' => $notification->title,
            'body' => $notification->body,
            'type' => $notification->type,
            'product_id' => $notification->product_id ? (int) $notification->product_id : null,
            'order_id' => $notification->order_id ? (int) $notification->order_id : null,
            'read_at' => optional($notification->read_at)?->toIso8601String(),
            'created_label' => $notification->created_at
                ? $notification->created_at->timezone(config('app.timezone'))->locale('id')->translatedFormat('d MMM yyyy, HH.mm')
                : '',
        ];
    }
}
