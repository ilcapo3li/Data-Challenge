<?php

namespace App\Repositories;

use App\Models\ProductVariant;
use App\Repositories\ProductVariationInterface;


class ProductVariationEloquent implements ProductVariationInterface
{

    public function __construct(
        private ProductVariant $productVariation,
    ) {
    }

    public function list(string $filter = "")
    {
        $this->productVariation->search($filter)->paginate();
    }

    public function getById(int $id)
    {
        $this->productVariation->find($id);
    }

    public function store(int $productId, object $data)
    {
        $this->productVariation->create([
            'product_id' => $productId,
            'name' => @$data->name,
            'value' => @$data->value,
            'color' => @$data->color,
            'material' => @$data->material,
            'quantity' => @$data->quantity,
            'additional_price' => @$data->additional_price,
        ]);
    }

    public function update(ProductVariant $productVariation, object $data)
    {
        $productVariation->update([

        ]);

    }    
}
