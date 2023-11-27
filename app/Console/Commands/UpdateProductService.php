<?php

namespace App\Console\Commands;

use App\Jobs\UpdateProductJob;
use App\Services\ProductService;
use Illuminate\Console\Command;

class UpdateProductService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:sync-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Third Party API to update a product';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new ProductService();
        $products = $service->getProducts();
        if (count($products)) {
            foreach ($products as $product) {
                UpdateProductJob::dispatch($product);
            }
        }
    }
}
