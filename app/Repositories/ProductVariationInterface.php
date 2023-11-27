<?php

namespace App\Repositories;

use App\Models\ProductVariant;

interface ProductVariationInterface
{
    public function list(string $filter = "");
    public function getById(int $id);
    public function store(int $productId, object $data);
    public function update(ProductVariant $productVariant, object $data);
}
