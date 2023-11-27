<?php

namespace App\Jobs;

use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\ProductInterface;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repositories\ProductVariationInterface;

class ConsumeProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Product repository pattern
            $repository = app(ProductInterface::class);
            $variantRepository = app(ProductVariationInterface::class);


            // Start transactions
            DB::beginTransaction();

                // Validate product uniqueness
                $product = $repository->getByIdOrSku($this->product->product_id, $this->product->sku);

                if ($product) {
                    $repository->update($product, $this->product);
                } else {
                    $product = $repository->store($this->product);

                    // insert variants 
                    if ($this->product->variations) {
                        foreach ($this->product->variations as $variation) {
                            $variantRepository->store($this->product->product_id, $variation);
                        }
                    }
                }

                // If status "deleted" softDelete
                if ($product->status == "deleted" && !$product->trashed()) {
                    $repository->delete($this->product->product_id);
                }

            DB::commit();       
        } catch (Throwable $e) {
            DB::rollBack();
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/error.log'),
            ])->error('System failed to create {id :' .$this->product->product_id. '}.', ['message' => $e->getMessage()]);
        }
    }
}