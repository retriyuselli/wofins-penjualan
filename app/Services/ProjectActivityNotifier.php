<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProjectActivityNotifier
{
    public function notify(Order $order, string $action): void
    {
        if (! in_array($action, ['created', 'updated', 'deleted'], true)) {
            return;
        }

        if (! Schema::hasTable('app_notifications') || ! Schema::hasColumn('app_notifications', 'order_id')) {
            return;
        }

        if ($action === 'updated') {
            $changes = array_diff(array_keys($order->getChanges()), ['last_edited_by', 'updated_at']);
            if ($changes === []) {
                return;
            }
        }

        $actor = $this->actor($order, $action);
        $projectName = $this->projectName($order);
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
                'product_id' => null,
                'order_id' => $order->id,
                'type' => 'project_'.$action,
                'title' => $this->title($action),
                'body' => mb_substr($this->body($projectName, $actorName, $action, $isActor), 0, 500),
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('app_notifications')->insert($rows);
        $push = app(PushNotifier::class);
        $push->send(array_map(static fn (array $row): array => [
            'user_id' => (int) $row['user_id'],
            'title' => $row['title'],
            'body' => $row['body'],
            'type' => $row['type'],
            'order_id' => $row['order_id'],
        ], $rows));
        $push->syncProjectActivity(
            $recipientIds->map(fn ($id) => (int) $id)->all(),
            (int) $order->id,
            $projectName,
            match ($action) {
                'updated' => 'update',
                'deleted' => 'end',
                default => 'start',
            },
            $this->title($action),
            $projectName,
        );
    }

    private function actor(Order $order, string $action): ?User
    {
        if ($action === 'deleted' && Auth::user() instanceof User) {
            return Auth::user();
        }

        if ($order->last_edited_by) {
            $editor = User::query()->find($order->last_edited_by);
            if ($editor instanceof User) {
                return $editor;
            }
        }

        return Auth::user() instanceof User ? Auth::user() : null;
    }

    private function projectName(Order $order): string
    {
        $name = trim((string) $order->name);
        if ($name !== '') {
            return $name;
        }

        $number = trim((string) $order->number);
        if ($number !== '') {
            return $number;
        }

        return 'Proyek #'.$order->id;
    }

    private function title(string $action): string
    {
        return match ($action) {
            'updated' => 'Proyek diperbarui',
            'deleted' => 'Proyek dihapus',
            default => 'Proyek baru',
        };
    }

    private function body(string $projectName, string $actorName, string $action, bool $isActor): string
    {
        if ($isActor) {
            return match ($action) {
                'updated' => $projectName.' berhasil diperbarui.',
                'deleted' => $projectName.' berhasil dihapus.',
                default => $projectName.' berhasil dibuat.',
            };
        }

        if ($actorName === '') {
            return match ($action) {
                'updated' => $projectName.' sudah diperbarui.',
                'deleted' => $projectName.' sudah dihapus.',
                default => $projectName.' sudah dibuat.',
            };
        }

        return match ($action) {
            'updated' => $projectName.' diperbarui oleh '.$actorName.'.',
            'deleted' => $projectName.' dihapus oleh '.$actorName.'.',
            default => $projectName.' dibuat oleh '.$actorName.'.',
        };
    }
}
