<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\ProductCreatedNotifier;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    public function created(Product $product): void
    {
        $this->notify($product, 'created');
    }

    public function updated(Product $product): void
    {
        $this->notify($product, 'updated');
    }

    public function deleted(Product $product): void
    {
        $this->notify($product, 'deleted');
    }

    private function notify(Product $product, string $action): void
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        try {
            app(ProductCreatedNotifier::class)->notify($product, $action);
        } catch (\Throwable $e) {
            Log::warning('product_notification_failed', [
                'product_id' => $product->id,
                'action' => $action,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
