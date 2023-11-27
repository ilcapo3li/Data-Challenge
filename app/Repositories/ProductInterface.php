<?php

namespace App\Repositories;

use App\Models\Product;

interface ProductInterface
{
    public function list(string $filter = "");
    public function getByProductId(int $productId);
    public function getBySku(string $sku);
    public function getByIdOrSku(int $productId, string $sku = null);
    public function store(object $data);
    public function update(Product $product, object $data);
    public function delete(int $productId);
}
