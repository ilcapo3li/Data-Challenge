<?php

namespace App\Providers;

use App\Models\Product;
use App\Observers\ProductObserver;
use App\Repositories\ProductEloquent;
use App\Repositories\ProductInterface;
use Illuminate\Support\ServiceProvider;
use App\Repositories\ProductVariationEloquent;
use App\Repositories\ProductVariationInterface;

class AppServiceProvider extends ServiceProvider
{
    private $repositories = [
        ProductInterface::class => ProductEloquent::class,
        ProductVariationInterface::class => ProductVariationEloquent::class
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
        
        foreach ($this->repositories as $interface => $eloquent) {
            $this->app->bind($interface, $eloquent);
        }
    }
}
