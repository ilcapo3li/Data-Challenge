<?php

namespace Tests\Unit;

use App\Models\Product;
use Tests\TestCase;


class ProductTest extends TestCase
{
    public function test_createProduct_withMandatoryData()
    {
        $product = Product::make([
            'product_id' => 1,
            'name' => 'Amazed Product',
        ]);

        $this->assertTrue(boolval($product));
    }

    public function test_factory_getProduct()
    {
        Product::factory()->count(3)->make();
        $product = Product::first();
        $this->assertDatabaseHas('products', ['product_id' => $product->id]);
    }
}
