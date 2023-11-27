<?php

namespace App\Jobs;

use Throwable;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\ProductInterface;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Repositories\ProductVariationInterface;

class UpdateProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product;

    /**
     * Create a new job instance.
     */
    public function __construct(array $product)
    {
        $this->product = json_decode(json_encode($product));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Product repository pattern
            $variantRepository = app(ProductVariationInterface::class);

            // Start transactions
            DB::beginTransaction();

                // Validate product uniqueness
                Product::updateOrCreate(
                    ['product_id' => $this->product->id], [
                    'name' => $this->product->name,
                    'image' => $this->product->image,
                    'price' => $this->product->price,
                ]);
                
                // insert variants 
                if ($this->product->variations) {
                    foreach ($this->product->variations as $variation) {
                        $variantRepository->store($this->product->id, $variation);
                    }
                }

            DB::commit();       
        } catch (Throwable $e) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/error.log'),
            ])->error('System failed to create {id :' .$this->product->id. '}.', ['message' => $e->getMessage()]);
        }
    }
}
