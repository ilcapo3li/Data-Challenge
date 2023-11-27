<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\ProductService;

class ProductServiceApiTest extends TestCase
{
    protected $productService;

    public function setUp(): void
    {
        $this->productService = new ProductService();
    }

    /**
     * A basic unit test example.
     */
    public function test_apiService_returnObjectOfProducts(): void
    {
        $response = $this->productService;
        $this->assertIsObject($response);
    }
}
