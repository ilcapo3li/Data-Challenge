<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     *
     * @param \App\Models\Product $product
     * @return void
     */
    public function created(Product $product)
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/created.log'),
        ])->info('System create {id :' .$product->product_id. '}.', ['sku' => $product->sku]);
    }

    /**
     * Handle the Product "updated" event.
     *
     * @param \App\Models\Product $product
     * @return void
     */
    public function updated(Product $product)
    {
        $message = match (true) {
            $product->wasChanged('price') && $product->wasChanged('quantity') => 
                __('System update :sku price & quantity', ['sku' => $product->sku]),
            $product->wasChanged('price') => __('System update :sku price', ['sku' => $product->sku]),
            $product->wasChanged('quantity') => __('System update : sku quantity', ['sku' => $product->sku]),
            default => __('System touch :sku', ['sku' => $product->sku])
        };
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/updated.log'),
        ])->info($message);
    }

    /**
     * Handle the Product "deleted" event.
     *
     * @param \App\Models\Product $product
     * @return void
     */
    public function deleted(Product $product)
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/deleted.log'),
        ])->info('System delete {id :' .$product->product_id. '}.', ['sku' => $product->sku]);
    }
}
