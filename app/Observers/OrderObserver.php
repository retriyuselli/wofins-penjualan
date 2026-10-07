<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\ProjectActivityNotifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "updating" event.
     */
    public function updating(Order $order): void
    {
        // Only update if user is authenticated
        if (Auth::check()) {
            $order->last_edited_by = Auth::id();
        }
    }

    /**
     * Handle the Order "creating" event.
     */
    public function creating(Order $order): void
    {
        // Set initial last_edited_by when creating
        if (Auth::check()) {
            $order->last_edited_by = Auth::id();
        }
    }

    public function created(Order $order): void
    {
        $this->notify($order, 'created');
    }

    public function updated(Order $order): void
    {
        $this->notify($order, 'updated');
    }

    public function deleted(Order $order): void
    {
        $this->notify($order, 'deleted');
    }

    private function notify(Order $order, string $action): void
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        try {
            app(ProjectActivityNotifier::class)->notify($order, $action);
        } catch (\Throwable $e) {
            Log::warning('project_notification_failed', [
                'order_id' => $order->id,
                'action' => $action,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
